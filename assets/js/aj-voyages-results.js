(function () {
  var page = document.querySelector('.aj-voyages-page--premium');
  if (!page) {
    return;
  }

  var sortSelect = page.querySelector('#aj-voyages-catalog-sort');
  var filtersToggle = page.querySelector('#aj-voyages-filters-toggle');
  var filterResetLinks = page.querySelectorAll('.aj-voyages-filters__reset, .aj-voyages-active-filters__reset');

  if (sortSelect && sortSelect.form) {
    sortSelect.addEventListener('change', function () {
      sortSelect.form.submit();
    });
  }

  filterResetLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      if (filtersToggle) {
        filtersToggle.checked = false;
      }
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && filtersToggle && filtersToggle.checked) {
      filtersToggle.checked = false;
      if (document.body) {
        document.body.style.overflow = '';
      }
    }
  });
})();
