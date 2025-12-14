document.addEventListener('DOMContentLoaded', function() {
  const addForm = document.getElementById('addCategoryForm');
  const addMsg = document.getElementById('addMsg');
  const tableWrap = document.getElementById('categoriesTableWrap');

  const editModalEl = document.getElementById('editModal');
  const editModal = new bootstrap.Modal(editModalEl);
  const editForm = document.getElementById('editCategoryForm');
  const editMsg = document.getElementById('editMsg');

  async function fetchCategories() {
    tableWrap.innerHTML = '<div class="text-center py-4 text-muted">Loading...</div>';
    try {
      const res = await fetch('../actions/fetch_category_action.php', { cache: 'no-store' });
      const data = await res.json();
      if (!data.status) {
        tableWrap.innerHTML = `<div class="text-danger p-3">${data.message}</div>`;
        return;
      }
      renderTable(data.categories || []);
    } catch (err) {
      tableWrap.innerHTML = `<div class="text-danger p-3">Error fetching categories</div>`;
    }
  }

  function renderTable(items) {
    if (!items.length) {
      tableWrap.innerHTML = '<div class="text-muted p-3">No categories yet.</div>';
      return;
    }
    let html = `<table class="table table-striped"><thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead><tbody>`;
    for (const c of items) {
      html += `<tr><td>${c.cat_id}</td><td>${escapeHtml(c.cat_name)}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary me-2" data-id="${c.cat_id}" data-name="${escapeHtml(c.cat_name)}" onclick="editCategory(this)">Edit</button>
          <button class="btn btn-sm btn-outline-danger" data-id="${c.cat_id}" onclick="deleteCategory(this)">Delete</button>
        </td></tr>`;
    }
    html += `</tbody></table>`;
    tableWrap.innerHTML = html;
  }

  addForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    addMsg.innerHTML = '';
    const formData = new FormData(addForm);
    try {
      const res = await fetch('../actions/add_category_action.php', {
        method: 'POST', body: formData
      });
      const data = await res.json();
      if (data.status) {
        addMsg.innerHTML = `<div class="alert alert-success small">${data.message}</div>`;
        addForm.reset();
        fetchCategories();
      } else {
        addMsg.innerHTML = `<div class="alert alert-danger small">${data.message}</div>`;
      }
    } catch (err) {
      addMsg.innerHTML = `<div class="alert alert-danger small">Request failed</div>`;
    }
  });

  // Expose edit and delete functions globally (called by inline onclick)
  window.editCategory = function(btn) {
    const id = btn.getAttribute('data-id');
    const name = btn.getAttribute('data-name');
    document.getElementById('edit_cat_id').value = id;
    document.getElementById('edit_cat_name').value = name;
    editMsg.innerHTML = '';
    editModal.show();
  };

  window.deleteCategory = async function(btn) {
    if (!confirm('Delete this category?')) return;
    const id = btn.getAttribute('data-id');
    const fd = new FormData();
    fd.append('cat_id', id);
    try {
      const res = await fetch('../actions/delete_category_action.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.status) {
        fetchCategories();
      } else {
        alert(data.message || 'Delete failed');
      }
    } catch (err) {
      alert('Request failed');
    }
  };

  editForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    editMsg.innerHTML = '';
    const fd = new FormData(editForm);
    try {
      const res = await fetch('../actions/update_category_action.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.status) {
        editMsg.innerHTML = `<div class="alert alert-success small">${data.message}</div>`;
        fetchCategories();
        setTimeout(()=>editModal.hide(), 700);
      } else {
        editMsg.innerHTML = `<div class="alert alert-danger small">${data.message}</div>`;
      }
    } catch (err) {
      editMsg.innerHTML = `<div class="alert alert-danger small">Request failed</div>`;
    }
  });

  function escapeHtml(unsafe) {
    return (unsafe + '').replace(/[&<>"']/g, function(m) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
    });
  }

  // initial load
  fetchCategories();
});
