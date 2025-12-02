const togglePasswordBtn = document.getElementById("togglePassword")
const passwordInput = document.getElementById("password")

// Toggle password visibility
togglePasswordBtn.addEventListener("click", (e) => {
  e.preventDefault()
  const type = passwordInput.type === "password" ? "text" : "password"
  passwordInput.type = type
})

// Password strength meter
passwordInput.addEventListener("input", (e) => {
  const strength = calculateStrength(e.target.value)
  updateStrengthBar(strength)
})

function calculateStrength(password) {
  let strength = 0
  if (password.length >= 8) strength++
  if (password.length >= 12) strength++
  if (/[a-z]/.test(password)) strength++
  if (/[A-Z]/.test(password)) strength++
  if (/[0-9]/.test(password)) strength++
  if (/[^a-zA-Z0-9]/.test(password)) strength++
  return strength
}

function updateStrengthBar(strength) {
  const bar = document.getElementById("strengthBar")
  const text = document.getElementById("strengthText")
  const widths = [0, 16, 32, 50, 66, 83, 100]
  const colors = ["#ef4444", "#f59e0b", "#f59e0b", "#f59e0b", "#10b981", "#10b981", "#10b981"]
  const texts = ["Too weak", "Weak", "Fair", "Good", "Strong", "Very strong", "Excellent"]

  bar.style.width = widths[strength] + "%"
  bar.style.backgroundColor = colors[strength]
  text.textContent = texts[strength]
}

// Form validation
document.getElementById("registrationForm").addEventListener("submit", async (e) => {
  e.preventDefault()

  const fullName = document.getElementById("fullName").value.trim()
  const email = document.getElementById("email").value.trim()
  const username = document.getElementById("username").value.trim()
  const password = document.getElementById("password").value
  const confirmPassword = document.getElementById("confirmPassword").value
  const phone = document.getElementById("phone").value.trim()
  const terms = document.getElementById("terms").checked

  if (!fullName || !email || !username || !password || !phone || !terms) {
    showError("All fields are required")
    return
  }

  if (password !== confirmPassword) {
    showError("Passwords do not match")
    return
  }

  if (password.length < 8) {
    showError("Password must be at least 8 characters")
    return
  }

  try {
    const formData = new FormData()
    formData.append("fullName", fullName)
    formData.append("email", email)
    formData.append("username", username)
    formData.append("password", password)
    formData.append("confirmPassword", confirmPassword)
    formData.append("phone", phone)

    const response = await fetch("../api/register.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      showSuccess("Registration successful! Redirecting to login...")
      setTimeout(() => {
        window.location.href = "index.html"
      }, 2000)
    } else {
      showError(result.message || "Registration failed")
    }
  } catch (error) {
    console.error("Error:", error)
    showError("An error occurred. Please try again.")
  }
})

function showError(message) {
  const errorDiv = document.getElementById("errorMessage")
  errorDiv.textContent = message
  errorDiv.classList.add("show")
  document.getElementById("successMessage").classList.remove("show")
}

function showSuccess(message) {
  const successDiv = document.getElementById("successMessage")
  successDiv.textContent = message
  successDiv.classList.add("show")
  document.getElementById("errorMessage").classList.remove("show")
}
