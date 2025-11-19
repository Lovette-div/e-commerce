document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('productForm');
  const productMsg = document.getElementById('productMsg');
  const productsTableWrap = document.getElementById('productsTableWrap');

  async function loadProducts() {
    productsTableWrap.innerHTML = '<div class="text-muted p-3">Loading...</div>';
    try {
      const res = await fetch('../actions/functions/fetch_product_action.php'); // implement fetch later
      const data = await res.json();
      if (!Array.isArray(data)) {
        productsTableWrap.innerHTML = '<div class="text-danger p-3">No products found</div>';
        return;
      }
      renderProducts(data);
    } catch (err) {
      productsTableWrap.innerHTML = '<div class="text-danger p-3">Failed to load products</div>';
    }
  }

  function renderProducts(items) {
    if (!items.length) {
      productsTableWrap.innerHTML = '<div class="text-muted p-3">No products yet.</div>';
      return;
    }
    let html = `<table class="table"><thead><tr><th>ID</th><th>Title</th><th>Price</th><th>Brand</th><th>Category</th><th>Actions</th></tr></thead><tbody>`;
    for (const p of items) {
      html += `<tr>
        <td>${p.product_id}</td>
        <td>${escapeHtml(p.title)}</td>
        <td>${parseFloat(p.price).toFixed(2)}</td>
        <td>${escapeHtml(p.brand_name || '')}</td>
        <td>${escapeHtml(p.cat_name || '')}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary" onclick="editProduct(${p.product_id})">Edit</button>
        </td>
      </tr>`;
    }
    html += `</tbody></table>`;
    productsTableWrap.innerHTML = html;
  }

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    productMsg.innerHTML = '';
    const fd = new FormData(form);
    // Determine add or update by product_id
    const product_id = fd.get('product_id');
    const url = (product_id && parseInt(product_id) > 0) ? '../actions/functions/update_product_action.php' : '../actions/functions/add_product_action.php';

    try {
      const res = await fetch(url, { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        productMsg.innerHTML = `<div class="alert alert-success small">${data.msg}</div>`;
        // if added, then upload images:
        const prodId = data.product_id || product_id;
        const filesInput = document.getElementById('images');
        if (filesInput && filesInput.files.length > 0 && prodId) {
          await uploadImages(prodId, filesInput.files);
        }
        form.reset();
        loadProducts();
      } else {
        productMsg.innerHTML = `<div class="alert alert-danger small">${data.msg}</div>`;
      }
    } catch (err) {
      productMsg.innerHTML = `<div class="alert alert-danger small">Request failed</div>`;
    }
  });

  async function uploadImages(productId, fileList) {
    const fd = new FormData();
    fd.append('product_id', productId);
    for (let i=0;i<fileList.length;i++){
      fd.append('images[]', fileList[i]);
    }
    try {
      const res = await fetch('../actions/functions/upload_product_image_action.php', { method:'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        // optional: show message or thumbnails
        productMsg.innerHTML += `<div class="alert alert-success small mt-1">${data.msg}</div>`;
      } else {
        productMsg.innerHTML += `<div class="alert alert-warning small mt-1">${data.msg}</div>`;
      }
    } catch (err) {
      productMsg.innerHTML += `<div class="alert alert-danger small mt-1">Image upload failed</div>`;
    }
  }

  // small helper
  function escapeHtml(unsafe) {
    return (unsafe + '').replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]; });
  }

  // initial load
  loadProducts();

  // expose editProduct (optional - will open modal or fill form)
  window.editProduct = function(productId) {
    // you can implement show-edit-modal: fetch product details and populate form or open modal
    (async()=>{
      try {
        const r = await fetch(`../actions/functions/fetch_single_product_action.php?product_id=${productId}`);
        const data = await r.json();
        if (data.success && data.product) {
          document.getElementById('product_id').value = data.product.product_id;
          document.getElementById('title').value = data.product.title;
          document.getElementById('price').value = data.product.price;
          document.getElementById('keyword').value = data.product.keyword;
          document.getElementById('description').value = data.product.description;
          document.getElementById('cat_id').value = data.product.cat_id;
          document.getElementById('brand_id').value = data.product.brand_id;
          window.scrollTo({top:0, behavior:'smooth'}); // shows form
        } else alert(data.msg || 'Cannot fetch product');
      } catch (err) { alert('Request failed'); }
    })();
  };

});
