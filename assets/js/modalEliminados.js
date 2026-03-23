document.addEventListener('DOMContentLoaded', function() {
  var btn = document.getElementById('btnEliminados');
  var modalEliminados = document.getElementById('modalEliminados');
  if (btn && modalEliminados) {
    btn.addEventListener('click', function() {
      var modal = new bootstrap.Modal(modalEliminados);
      modal.show();
    });
  }
});
