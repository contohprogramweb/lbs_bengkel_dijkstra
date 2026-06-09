            </div>
        </div>
    </div>

    <!-- JS Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            if ($('.datatable').length) {
                $('.datatable').DataTable({
                    responsive: true,
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' }
                });
            }
            
            // Initialize Select2
            if ($('.select2').length) {
                $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
            }
        });
        
        // Global delete confirmation function using SweetAlert
        function confirmDelete(url, title, message) {
            Swal.fire({
                title: title || 'Apakah Anda yakin?',
                text: message || 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
        
        // Global confirm action function using SweetAlert
        function confirmAction(url, title, message, confirmText, confirmColor) {
            Swal.fire({
                title: title || 'Apakah Anda yakin?',
                text: message || 'Anda akan melakukan aksi ini!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: confirmColor || '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText || 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>
    
    <?php if (isset($custom_scripts)): ?>
        <?php echo $custom_scripts; ?>
    <?php endif; ?>
</body>
</html>
