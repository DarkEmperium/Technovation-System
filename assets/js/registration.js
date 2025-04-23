const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});

window.addEventListener("load", function () {
    var toastEl = document.getElementById("toastMessage");
    if (toastEl) {
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
});

function showpassword() {
    var x = document.getElementById("password-input");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}

// Show alert if exists
document.addEventListener('DOMContentLoaded', function() {
    var customAlert = document.getElementById('customAlert');
    if (customAlert) {
        customAlert.classList.add('show');

        // Automatically hide after 4 seconds
        setTimeout(function() {
            customAlert.classList.remove('show');
        }, 4000);
    }

    // Close alert manually
    var closeAlert = document.getElementById('closeAlert');
    if (closeAlert) {
        closeAlert.addEventListener('click', function() {
            customAlert.classList.remove('show');
        });
    }
});
