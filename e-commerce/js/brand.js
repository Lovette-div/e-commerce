document.addEventListener('DOMContentLoaded', function() {
  const addForm = document.getElementById('brandForm');
  const addMsg = document.createElement('div');
  addMsg.id = 'addMsg';
  addForm.appendChild(addMsg);

  const tableBody = document.querySelector('#brandTable tbody');

  const editModalEl = document.getElementById('editModal');
  const editModal = new bootstrap.Modal(editModalEl);
  const editForm = document.getElementById('editBrandForm');
  const editMsg = document.getElementById('editMsg');

  // ---- Fetch brands ----
  async function fetchBrands() {
    tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>';
    try {
      const res = await fetch('../actions/functions/fetch_brand_action.php', { cache: 'no-store' });
      const data = await res.json();
      if (!Array.isArray(data)) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-danger">No brand data found</td></tr>`;
        return;
      }
      renderTable(data);
    } catch (err) {
      tableBody.innerHTML = `<tr><td colspan="4" class="text-danger">Error fetching brands</td></tr>`;
    }
  }

  // ---- Render table ----
  function renderTable(items) {
    if (!items.length) {
      tableBody.innerHTML = '<tr><td colspan="4" class="text-muted text-center py-3">No brands yet.</td></tr>';
      return;
    }
    let html = '';
    for (const b of items) {
      html += `<tr>
        <td>${b.brand_id}</td>
        <td>${escapeHtml(b.brand_name)}</td>
        <td>${escapeHtml(b.cat_name)}</td>
        <td>
          <button class="btn btn-edit" 
            data-id="${b.brand_id}" 
            data-name="${escapeHtml(b.brand_name)}" 
            data-cat="${b.cat_id}" 
            onclick="openEditModal(this)">Edit</button>
          <button class="btn btn-delete" 
            data-id="${b.brand_id}" 
            onclick="deleteBrand(this)">Delete</button>
        </td>
      </tr>`;
    }
    tableBody.innerHTML = html;
  }

  // ---- Add brand ----
  addForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    addMsg.innerHTML = '';
    const fd = new FormData(addForm);
    try {
      const res = await fetch('../actions/functions/add_brand_action.php', {
        method: 'POST',
        body: fd
      });
      const data = await res.json();
      if (data.status || data.success) {
        addMsg.innerHTML = `<div class="alert alert-success small mt-2">${data.message || data.msg}</div>`;
        addForm.reset();
        fetchBrands();
      } else {
        addMsg.innerHTML = `<div class="alert alert-danger small mt-2">${data.message || data.msg}</div>`;
      }
    } catch (err) {
      addMsg.innerHTML = `<div class="alert alert-danger small mt-2">Request failed</div>`;
    }
  });

  // ---- Edit brand (modal popup only — add form stays empty) ----
  window.openEditModal = async function(btn) {
    const id = btn.getAttribute('data-id');
    const name = btn.getAttribute('data-name');
    const cat = btn.getAttribute('data-cat');

    // populate modal fields only (NOT the add form)
    document.getElementById('edit_brand_id').value = id;
    document.getElementById('edit_brand_name').value = name;
    editMsg.innerHTML = '';

    // Load category dropdown for modal
    try {
      const res = await fetch('../actions/fetch_category_action.php');
      const data = await res.json();
      const select = document.getElementById('edit_cat_id');
      select.innerHTML = '';
      (data.categories || []).forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.cat_id;
        opt.textContent = c.cat_name;
        if (parseInt(c.cat_id) === parseInt(cat)) opt.selected = true;
        select.appendChild(opt);
      });
    } catch (err) {
      console.error('Category load failed for modal');
    }

    // show modal
    editModal.show();
  };

  // ---- Update brand ----
  editForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    editMsg.innerHTML = '';
    const fd = new FormData(editForm);
    try {
      const res = await fetch('../actions/functions/update_brand_action.php', {
        method: 'POST',
        body: fd
      });
      const data = await res.json();
      if (data.status) {
        editMsg.innerHTML = `<div class="alert alert-success small">${data.message}</div>`;
        fetchBrands();
        setTimeout(() => editModal.hide(), 700);
      } else {
        editMsg.innerHTML = `<div class="alert alert-danger small">${data.message}</div>`;
      }
    } catch (err) {
      editMsg.innerHTML = `<div class="alert alert-danger small">Request failed</div>`;
    }
  });

  // ---- Delete brand ----
  window.deleteBrand = async function(btn) {
    if (!confirm('Delete this brand?')) return;
    const id = btn.getAttribute('data-id');
    const fd = new FormData();
    fd.append('brand_id', id);
    try {
      const res = await fetch('../actions/functions/delete_brand_action.php', {
        method: 'POST',
        body: fd
      });
      const data = await res.json();
      if (data.status) fetchBrands();
      else alert(data.message || data.msg || 'Delete failed');
    } catch (err) {
      alert('Request failed');
    }
  };

  // ---- Escape HTML ----
  function escapeHtml(unsafe) {
    return (unsafe + '').replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));
  }

  // ---- Initial load ----
  fetchBrands();
});
