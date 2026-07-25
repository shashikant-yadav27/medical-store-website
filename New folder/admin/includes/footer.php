<?php
// admin/includes/footer.php
?>
                </div> <!-- End container-fluid -->
            </div> <!-- End main-content -->
            
            <!-- Footer -->
            <footer class="bg-white p-3 text-center border-top mt-auto">
                <small class="text-muted">Medical Store Admin Panel &copy; <?php echo date('Y'); ?></small>
            </footer>
        </div> <!-- End flex-grow-1 -->
    </div> <!-- End d-flex -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Simple sidebar toggle for mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            var sidebar = document.querySelector('.sidebar');
            if(sidebar.style.display === 'none' || sidebar.style.display === '') {
                sidebar.style.display = 'block';
                sidebar.style.position = 'absolute';
                sidebar.style.zIndex = '1000';
            } else {
                sidebar.style.display = 'none';
            }
        });
    </script>
</body>
</html>
