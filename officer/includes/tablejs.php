<!-- jQuery -->
<script src="js/jquery.min.js"></script>

<!-- Bootstrap 4.6 -->
<script src="js/bootstrap.bundle.min.js"></script>

<!-- DataTables  & Plugins -->
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap4.min.js"></script>
<script src="js/dataTables.responsive.min.js"></script>
<script src="js/responsive.bootstrap4.min.js"></script>
<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.bootstrap4.min.js"></script>
<script src="js/jszip.min.js"></script>
<script src="js/pdfmake.min.js"></script>
<script src="js/vfs_fonts.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script src="js/buttons.print.min.js"></script>
<script src="js/buttons.colVis.min.js"></script>

<!-- AdminLTE App -->
<script src="js/adminlte.min.js?v=3.2.0"></script>

<script>
    $(function() {
        $("#table1").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "excel", "colvis"]
        }).buttons().container().appendTo('#table1_wrapper .col-md-6:eq(0)');
        
        $('#table1_wrapper').css('margin-bottom', '40px');
    });
</script>