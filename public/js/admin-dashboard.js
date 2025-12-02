// Admin Dashboard Functions

function openModal(modalId) {
  document.getElementById(modalId).classList.add("active")
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove("active")
}

function showSection(sectionId) {
  document.querySelectorAll(".section").forEach((s) => s.classList.remove("active"))
  document.querySelectorAll(".nav-item").forEach((n) => n.classList.remove("active"))

  document.getElementById(sectionId).classList.add("active")
  event.target.closest(".nav-item").classList.add("active")
}

function logout() {
  if (confirm("Logout from admin panel?")) {
    fetch("../api/auth.php?action=logout", { method: "POST" }).then(() => (window.location.href = "login.php"))
  }
}

async function loadAdminDashboard() {
  try {
    const usersRes = await fetch("../api/users.php?action=list")
    const usersData = await usersRes.json()

    if (usersData.success) {
      document.getElementById("totalUsers").textContent = usersData.data.length
      loadUsersTable(usersData.data)
    }
  } catch (error) {
    console.error("[v0] Error loading users:", error)
  }

  try {
    const livestockRes = await fetch("../api/livestock.php?action=list")
    const livestockData = await livestockRes.json()

    if (livestockData.success) {
      document.getElementById("totalLivestock").textContent = livestockData.data.length
    }
  } catch (error) {
    console.error("[v0] Error loading livestock:", error)
  }
}

function loadUsersTable(data) {
  const tbody = document.getElementById("usersTable")
  tbody.innerHTML = data
    .map(
      (user) => `
        <tr>
            <td>${user.username}</td>
            <td>${user.email}</td>
            <td>${user.full_name}</td>
            <td><span class="badge ${user.role === "admin" ? "danger" : "healthy"}">${user.role}</span></td>
            <td><span class="badge ${user.status === "active" ? "healthy" : "danger"}">${user.status}</span></td>
            <td>
                <button class="btn-text" onclick="deactivateUser(${user.id})">Deactivate</button>
            </td>
        </tr>
    `,
    )
    .join("")
}

async function deactivateUser(id) {
  if (!confirm("Deactivate this user?")) return

  try {
    const response = await fetch("../api/users.php?action=deactivate", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ user_id: id }),
    })

    const result = await response.json()
    alert(result.message)
    loadAdminDashboard()
  } catch (error) {
    console.error("[v0] Error:", error)
  }
}

document.addEventListener("DOMContentLoaded", () => {
  loadAdminDashboard()
})
