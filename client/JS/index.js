document.addEventListener("DOMContentLoaded", init);

const BASE_URI = "http://localhost:8000/kahuna/api/";
let products = [];

function init() {
    loginUser();
}

function showLoginError(message) {
    const loginForm = document.getElementById("loginForm");
    const existingError = loginForm.querySelector('.alert');
    if (existingError) {
        existingError.remove();
    }
    
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger mt-3';
    alert.textContent = message;
    loginForm.appendChild(alert);
}

function loginUser() {
    document.getElementById("loginForm").addEventListener("submit", async (evt) => {
        evt.preventDefault();
        
        const username = document.getElementById("loginUsername").value;
        const password = document.getElementById("loginPassword").value;
        
        if (!username || !password) {
            showLoginError("Please enter both username and password");
            return;
        }
        
        const submitButton = evt.target.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);

            const response = await fetch(`${BASE_URI}login`, {
                mode: "cors",
                method: "POST",
                body: formData
            });

            const data = await response.json();

            if (!response.ok || data.error) {
                throw new Error(data.error || "Invalid username or password");
            }

            if (!data.data || !data.data.token || !data.data.user || !data.data.username || !data.data.accessLevel) {
                throw new Error("Invalid server response: Missing required data");
            }

            localStorage.setItem("token", data.data.token);
            localStorage.setItem("user", data.data.user);
            localStorage.setItem("username", data.data.username);
            localStorage.setItem("accessLevel", data.data.accessLevel);
            
            window.location.href = "products.html";

        } catch (err) {
            showLoginError(err.message || "Invalid username or password");
        } finally {
            submitButton.disabled = false;
        }
    });
}

function bindHome() {
  document.getElementById("loginForm").addEventListener("submit", (evt) => {
    evt.preventDefault();
    todoData = new FormData(document.getElementById("loginForm"));
    checkAndRedirect("home", () => {
      fetch(`${BASE_URI}product`, {
        mode: "cors",
        method: "POST",
        headers: {
          "X-Api-Key": localStorage.getItem("token"),
          "X-Api-User": localStorage.getItem("user"),
        },
        body: todoData,
      })
        .then(loadTodos)
        .catch((err) => console.error(err));
    });
  });
}

async function isValidToken(token, user, cb) {
    if (!token || !user) return cb(false);
    
    try {
        const res = await fetch(`${BASE_URI}token`, {
            headers: {
                "X-Api-Key": token,
                "X-Api-User": user,
            },
        });
        const data = await res.json();
        return cb(data.data && data.data.valid === true);
    } catch (err) {
        console.error("Token validation error:", err);
        return cb(false);
    }
}

function checkAndRedirect(redirect = null, cb = null) {
    let token = localStorage.getItem("token");

    if (!token) {
        showView("login").then(() => bindLogin(redirect, cb));
    } else {
        let user = localStorage.getItem("user");
        isValidToken(token, user, (valid) => {
            if (valid) {
                showView(redirect).then(cb);
            } else {
                showView(redirect).then(cb);
            }
        });
    }
}
