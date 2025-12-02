// User Dashboard Functions
function openModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.add("active")
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.remove("active")
  }
}

// Initialize dashboard
document.addEventListener("DOMContentLoaded", () => {
  loadDashboardData()
  setupEventListeners()
})

function setupEventListeners() {
  // Sidebar navigation
  document.querySelectorAll(".nav-item").forEach((item) => {
    item.addEventListener("click", (e) => {
      e.preventDefault()
      const section = item.dataset.section
      switchSection(section)
      document.querySelectorAll(".nav-item").forEach((n) => n.classList.remove("active"))
      item.classList.add("active")
    })
  })

  // Logout
  document.getElementById("logoutBtn").addEventListener("click", () => {
    if (confirm("Are you sure you want to logout?")) {
      window.location.href = "index.html"
    }
  })

  // Form submissions
  document.getElementById("addLivestockForm").addEventListener("submit", async (e) => {
    e.preventDefault()
    const formData = new FormData(e.target)
    formData.append("action", "add")

    try {
      const response = await fetch("../api/livestock.php", {
        method: "POST",
        body: formData,
      })
      const result = await response.json()
      if (result.success) {
        alert("Livestock added successfully")
        closeModal("addLivestockModal")
        document.getElementById("addLivestockForm").reset()
        loadLivestock()
      }
    } catch (error) {
      console.error("Error:", error)
    }
  })

  document.getElementById("addProductionForm").addEventListener("submit", async (e) => {
    e.preventDefault()
    const formData = new FormData(e.target)
    formData.append("action", "add")

    try {
      const response = await fetch("../api/production.php", {
        method: "POST",
        body: formData,
      })
      const result = await response.json()
      if (result.success) {
        alert("Production record added")
        closeModal("addProductionModal")
        document.getElementById("addProductionForm").reset()
        loadProduction()
      }
    } catch (error) {
      console.error("Error:", error)
    }
  })
}

function switchSection(section) {
  document.querySelectorAll(".section").forEach((s) => s.classList.remove("active"))
  const selectedSection = document.getElementById(section)
  if (selectedSection) {
    selectedSection.classList.add("active")
  }
  document.getElementById("pageTitle").textContent = section.charAt(0).toUpperCase() + section.slice(1)
}

async function loadDashboardData() {
  try {
    const livestockRes = await fetch("../api/livestock.php?action=fetch")
    const livestockData = await livestockRes.json()
    document.getElementById("myLivestock").textContent = livestockData.data?.length || 0

    document.getElementById("userName").textContent = "Farm Staff"

    loadLivestock()
    loadProduction()
  } catch (error) {
    console.error("Error:", error)
  }
}

async function loadLivestock() {
  try {
    const response = await fetch("../api/livestock.php?action=fetch")
    const data = await response.json()

    if (data.success) {
      const tbody = document.getElementById("livestockTable")
      tbody.innerHTML =
        data.data
          .map(
            (item) => `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.type}</td>
                    <td>${item.breed}</td>
                    <td>${item.age}</td>
                    <td><span class="badge ${item.status === "Healthy" ? "healthy" : "warning"}">${item.status}</span></td>
                    <td><button class="btn-text" onclick="deleteLivestock(${item.id})">Delete</button></td>
                </tr>
            `,
          )
          .join("") || '<tr><td colspan="6" class="text-muted">No records</td></tr>'
    }
  } catch (error) {
    console.error("Error:", error)
  }
}

async function loadProduction() {
  try {
    const response = await fetch("../api/production.php?action=fetch")
    const data = await response.json()

    if (data.success) {
      const tbody = document.getElementById("productionTable")
      tbody.innerHTML =
        data.data
          .map(
            (item) => `
                <tr>
                    <td>${item.date}</td>
                    <td>${item.type}</td>
                    <td>${item.amount}</td>
                    <td>${item.unit}</td>
                    <td>${item.notes || "-"}</td>
                </tr>
            `,
          )
          .join("") || '<tr><td colspan="5" class="text-muted">No records</td></tr>'
    }
  } catch (error) {
    console.error("Error:", error)
  }
}

async function deleteLivestock(id) {
  if (!confirm("Delete this record?")) return

  try {
    const formData = new FormData()
    formData.append("action", "delete")
    formData.append("id", id)

    const response = await fetch("../api/livestock.php", {
      method: "POST",
      body: formData,
    })
    const result = await response.json()
    if (result.success) {
      loadLivestock()
    }
  } catch (error) {
    console.error("Error:", error)
  }
}
