<footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; 2021 <a href="#">DEVELOPMENT TEAM</a>.</strong> All rights reserved.
</footer>

  {{-- <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside> --}}

  <!-- /.control-sidebar -->
<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{ asset('template') }}/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('template') }}/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('template') }}/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="{{ asset('template') }}/dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('template') }}/dist/js/demo.js"></script>
<!-- SOFTDELETE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<!-- date-range-picker -->
<script src="{{ asset('template') }}/plugins/moment/moment.min.js"></script>
<script src="{{ asset('template') }}/plugins/daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.2.0/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">

     $('.show_confirm').click(function(event) {
          var form =  $(this).closest("form");
          var name = $(this).data("name");
          event.preventDefault();
          swal({
              title: `Are you sure you want to delete this record?`,
              text: "If you delete this, it will be gone forever.",
              icon: "warning",
              buttons: true,
              dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              form.submit();
            }
          });

      });

      $('#tanggalBulkDelete').daterangepicker({
        parentEl: "#bulkDelete .modal-body"
    });

    $("#tanggalVisit").datepicker({
        format: "yyyy-mm-dd",
        startDate: '-1d',
    });

    $("#tanggalFilter").daterangepicker();

    $(document).ready(function() {
        // Mendapatkan tanggal saat ini
        var today = new Date();

        // Mendapatkan bulan sebelumnya
        var previousMonth = new Date(today.getFullYear(), today.getMonth(), 1);

        // Format bulan sebelumnya menjadi YYYY-MM untuk dimasukkan ke atribut max
        var formattedPreviousMonth = previousMonth.toISOString().slice(0, 7);

        // Set nilai atribut max pada elemen input month
        $('#daterangesearch').attr('max', formattedPreviousMonth);

        // Jika URL parameter daterangesearch tidak disediakan, atur nilai input dengan bulan sebelumnya
        var urlParams = new URLSearchParams(window.location.search);
        var daterangeValue = urlParams.get('daterangesearch');
        if (daterangeValue === null) {
            $('#daterangesearch').val(formattedPreviousMonth);
        } else {
            $('#daterangesearch').val(daterangeValue);
        }

        // Ketika nilai input month berubah
        $('#daterangesearch').on('change', function() {
            // Mendapatkan nilai dari input
            var selectedMonth = $(this).val();

            // Melakukan pengalihan ke action="/noo" dengan menggunakan window.location.href
            window.location.href = "/report/noo?daterangesearch=" + selectedMonth;
        });
    });

</script>
</body>
</html>
