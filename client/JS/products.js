document.addEventListener('DOMContentLoaded', init);

const BASE_URI = 'http://localhost:8000/kahuna/api/';
let products = [];

function init() {
    const token = localStorage.getItem("token");
    const user = localStorage.getItem("user");
    const accessLevel = localStorage.getItem("accessLevel");
    const username = localStorage.getItem("username");
    
    if (!token || !user) {
        window.location.replace("index.html");
        return;
    }

    const addProductSection = document.getElementById('addProductSection');
    if (accessLevel === 'admin') {
        addProductSection.style.display = 'block';
    } else {
        addProductSection.style.display = 'none';
    }

    const userInfo = document.getElementById('userInfo');
    if (userInfo) {
        userInfo.textContent = `Welcome ${username} (${accessLevel})`;
        userInfo.className = 'me-3 text-light';
    }

    loadProducts();
    bindAddProduct();
    bindLogout();
}

function bindLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.onclick = function(e) {
            e.preventDefault();
            localStorage.clear();
            window.location.replace("./index.html");
        };
    }
}

function loadProducts(){
    fetch(`${BASE_URI}product`, {
        mode: 'cors',
        method: 'GET',
        headers: {
            "X-Api-Key": localStorage.getItem("token"),
            "X-Api-User": localStorage.getItem("user")
        }
    })
    .then(res => res.json())
    .then(res => {
        if (res.error) {
            alert(res.error);
            return;
        }
        products = res.data || [];
        displayProducts();
    })
    .catch(err => {
        alert("Failed to load products. Please try again.");
    });
}

function displayProducts() {
    const productList = document.getElementById('productlist');
    if (!productList) return;

    if (products.length === 0) {
        productList.innerHTML = '<div class="alert alert-info">No products found.</div>';
        return;
    }

    const table = document.createElement('table');
    table.className = 'table table-striped';
    
    const thead = document.createElement('thead');
    thead.innerHTML = `
        <tr>
            <th>Serial Number</th>
            <th>Name</th>
            <th>Warranty Length (months)</th>
        </tr>
    `;
    table.appendChild(thead);
    
    const tbody = document.createElement('tbody');
    products.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product.serial}</td>
            <td>${product.name}</td>
            <td>${product.warrantyLength}</td>
        `;
        tbody.appendChild(row);
    });
    table.appendChild(tbody);
    
    productList.innerHTML = '';
    productList.appendChild(table);
}

function showMessage(message, type = 'success') {
    const form = document.getElementById('productForm');
    const existingAlert = form.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} mt-3`;
    alert.textContent = message;
    form.appendChild(alert);
    
    setTimeout(() => alert.remove(), 3000);
}

function bindAddProduct() {
    const form = document.getElementById('productForm');
    if (!form) return;

    form.addEventListener('submit', (evt) => {
        evt.preventDefault();
        const productData = new FormData(form);
        
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        
        fetch(`${BASE_URI}product`, {
            mode: 'cors',
            method: 'POST',
            headers: {
                "X-Api-Key": localStorage.getItem("token"),
                "X-Api-User": localStorage.getItem("user")
            },
            body: productData
        })
        .then(res => res.json())
        .then(res => {
            if (res.error) {
                showMessage(res.error, 'danger');
                return;
            }
            form.reset();
            showMessage('Product added successfully!');
            loadProducts();
        })
        .catch(err => {
            showMessage('Failed to add product. Please try again.', 'danger');
        })
        .finally(() => {
            submitButton.disabled = false;
        });
    });
}