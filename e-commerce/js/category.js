document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("addCategoryForm");
    const tableBody = document.querySelector("#categoryTable tbody");

    // Fetch categories
    function loadCategories() {
        fetch("../actions/fetch_category_action.php")
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = "";
                data.forEach(cat => {
                    let row = `
                        <tr>
                            <td>${cat.id}</td>
                            <td><input type="text" value="${cat.name}" data-id="${cat.id}" class="edit-name"></td>
                            <td>
                                <button class="updateBtn" data-id="${cat.id}">Update</button>
                                <button class="deleteBtn" data-id="${cat.id}">Delete</button>
                            </td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            });
    }

    loadCategories();

    // Add category
    form.addEventListener("submit", e => {
        e.preventDefault();
        const formData = new FormData(form);

        fetch("../actions/add_category_action.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(alert)
        .then(loadCategories);
    });

    // Update / Delete category
    tableBody.addEventListener("click", e => {
        if (e.target.classList.contains("updateBtn")) {
            let id = e.target.dataset.id;
            let name = e.target.closest("tr").querySelector(".edit-name").value;

            fetch("../actions/update_category_action.php", {
                method: "POST",
                body: new URLSearchParams({ id, name })
            })
            .then(res => res.text())
            .then(alert)
            .then(loadCategories);
        }

        if (e.target.classList.contains("deleteBtn")) {
            let id = e.target.dataset.id;

            fetch("../actions/delete_category_action.php", {
                method: "POST",
                body: new URLSearchParams({ id })
            })
            .then(res => res.text())
            .then(alert)
            .then(loadCategories);
        }
    });
});
