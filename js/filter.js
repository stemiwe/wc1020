// Filter tables.
function tableFilter() {
    const select = document.getElementById("table-filter");
    const selectedValue = select.value;

    // Get current URL and its params
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);

    // Update (or add) the 'value' param
    if (selectedValue) {
        params.set('value', selectedValue); // Overrides if 'value' exists
    } else {
        params.delete('value'); // Remove if empty selection
    }

    // Rebuild URL with updated params
    url.search = params.toString();
    window.location.href = url.toString();
}

// Preselect current filter on page load
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('value')) {
        document.getElementById("table-filter").value = params.get('value');
    }
});

// Prev/next arrows for filter.
document.addEventListener("DOMContentLoaded", function() {
  const select = document.getElementById("table-filter");
  const prevBtn = document.getElementById("filter-prev");
  const nextBtn = document.getElementById("filter-next");

  // Initialize buttons based on current selection
  updateButtonStates();

  // Previous button: Select the previous option
  prevBtn.addEventListener("click", function() {
    if (select.selectedIndex > 0) {
      select.selectedIndex--;
      updateButtonStates();
      updateUrlWithFilter();
    }
  });

  // Next button: Select the next option
  nextBtn.addEventListener("click", function() {
    if (select.selectedIndex < select.options.length - 1) {
      select.selectedIndex++;
      updateButtonStates();
      updateUrlWithFilter();
    }
  });

  // Select dropdown: Update buttons and URL on manual change
  select.addEventListener("change", function() {
    updateButtonStates();
    updateUrlWithFilter();
  });

  // Helper: Update prev/next button states (add/remove 'inactive')
  function updateButtonStates() {
    prevBtn.classList.toggle("inactive", select.selectedIndex <= 0);
    nextBtn.classList.toggle("inactive", select.selectedIndex >= select.options.length - 1);
  }

  // Helper: Update URL with current filter value
  function updateUrlWithFilter() {
    const params = new URLSearchParams(window.location.search);
    const value = select.options[select.selectedIndex].value;

    if (value) {
      params.set("value", value);
    } else {
      params.delete("value");
    }

    // Reset pagination if needed (optional)
    params.delete("page");

    window.location.href = window.location.pathname + "?" + params.toString();
  }
});