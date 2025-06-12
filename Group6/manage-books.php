<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin-login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                $title = $_POST['title'];
                $author = $_POST['author'];
                $isbn = $_POST['isbn'];
                $category = $_POST['category'];
                $description = $_POST['description'];
                $quantity = (int)$_POST['quantity'];
                $publication_year = (int)$_POST['publication_year'];
                
                // Handle file upload
                $cover_image = null;
                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['cover_image']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $target_dir = "assets/images/books/";
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }
                        
                        $new_filename = uniqid() . "." . $ext;
                        $target_file = $target_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
                            $cover_image = $target_file;
                        }
                    }
                }
                
                if ($_POST['action'] == 'add') {
                    $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, category, description, quantity, cover_image, publication_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $author, $isbn, $category, $description, $quantity, $cover_image, $publication_year]);
                } else {
                    $id = $_POST['book_id'];
                    if ($cover_image) {
                        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, category = ?, description = ?, quantity = ?, cover_image = ?, publication_year = ? WHERE id = ?");
                        $stmt->execute([$title, $author, $isbn, $category, $description, $quantity, $cover_image, $publication_year, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, category = ?, description = ?, quantity = ?, publication_year = ? WHERE id = ?");
                        $stmt->execute([$title, $author, $isbn, $category, $description, $quantity, $publication_year, $id]);
                    }
                }
                break;
                
            case 'delete':
                $id = $_POST['book_id'];
                // Check if book can be deleted (no active borrowings)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE book_id = ? AND status = 'borrowed'");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() == 0) {
                    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
                    $stmt->execute([$id]);
                }
                break;
        }
        
        header("Location: manage-books.php");
        exit();
    }
}

// Fetch all books
$stmt = $pdo->query("SELECT * FROM books ORDER BY title");
$books = $stmt->fetchAll();

// Fetch distinct categories
$stmt = $pdo->query("SELECT DISTINCT category FROM books ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Books - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Fixed Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="admin-dashboard.php">
                <i class="fas fa-user-shield me-2"></i>Library Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="admin-dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="manage-books.php"><i class="fas fa-book me-1"></i> Manage Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage-users.php"><i class="fas fa-users me-1"></i> Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-borrowings.php"><i class="fas fa-list me-1"></i> Manage Borrowings</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="admin-container py-4" style="margin-top: 80px;">
        <div class="admin-header d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-book me-2"></i>Manage Books</h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                    <i class="fas fa-plus me-2"></i>Add New Book
                </button>
            </div>
        </div>
        <!-- Books Table -->
        <div class="card admin-card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover admin-table">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($book['cover_image'] ?? 'assets/images/default-book.jpg'); ?>" 
                                         alt="Book cover" class="img-thumbnail" style="width: 50px;">
                                </td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($book['category']); ?></td>
                                <td><?php echo htmlspecialchars($book['quantity']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-book" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editBookModal"
                                            data-book='<?php echo json_encode($book); ?>'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-book"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteBookModal"
                                            data-book-id="<?php echo $book['id']; ?>"
                                            data-book-title="<?php echo htmlspecialchars($book['title']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="manage-books.php" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Author</label>
                                <input type="text" name="author" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">ISBN</label>
                                <input type="text" name="isbn" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" list="categories" required>
                                <datalist id="categories">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Publication Year</label>
                                <input type="number" name="publication_year" class="form-control" min="1000" max="<?php echo date('Y'); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cover Image</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div class="modal fade" id="editBookModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="manage-books.php" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="book_id" id="edit_book_id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" id="edit_title" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Author</label>
                                <input type="text" name="author" id="edit_author" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">ISBN</label>
                                <input type="text" name="isbn" id="edit_isbn" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" id="edit_category" class="form-control" list="categories" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" id="edit_quantity" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Publication Year</label>
                                <input type="number" name="publication_year" id="edit_publication_year" class="form-control" min="1000" max="<?php echo date('Y'); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cover Image</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Book Modal -->
    <div class="modal fade" id="deleteBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete "<span id="delete_book_title"></span>"?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form action="manage-books.php" method="post">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="book_id" id="delete_book_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Book</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit book modal
        document.querySelectorAll('.edit-book').forEach(button => {
            button.addEventListener('click', function() {
                const book = JSON.parse(this.dataset.book);
                document.getElementById('edit_book_id').value = book.id;
                document.getElementById('edit_title').value = book.title;
                document.getElementById('edit_author').value = book.author;
                document.getElementById('edit_isbn').value = book.isbn;
                document.getElementById('edit_category').value = book.category;
                document.getElementById('edit_quantity').value = book.quantity;
                document.getElementById('edit_publication_year').value = book.publication_year;
                document.getElementById('edit_description').value = book.description;
            });
        });

        // Handle delete book modal
        document.querySelectorAll('.delete-book').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_book_id').value = this.dataset.bookId;
                document.getElementById('delete_book_title').textContent = this.dataset.bookTitle;
            });
        });
    </script>
</body>
</html>