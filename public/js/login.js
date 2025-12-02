document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault()

  const email = document.getElementById("email").value.trim()
  const password = document.getElementById("password").value
  const userType = document.getElementById("userType").value

  if (!email || !password || !userType) {
    alert("Please fill all fields")
    return
  }

  try {
    const formData = new FormData()
    formData.append("email", email)
    formData.append("password", password)
    formData.append("userType", userType)

    const response = await fetch("../api/login.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      // Redirect to dashboard
      window.location.href = result.redirect
    } else {
      alert(result.message || "Login failed")
    }
  } catch (error) {
    console.error("Error:", error)
    alert("An error occurred. Please try again.")
  }
})
