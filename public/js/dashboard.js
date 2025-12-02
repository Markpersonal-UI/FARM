// Dashboard Functions

// Modal Management
function openModal(modalId) {
  document.getElementById(modalId).classList.add("active")
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove("active")
}

// Section Navigation
function showSection(sectionId) {
  document.querySelectorAll(".section").forEach((s) => s.classList.remove("active"))
  document.querySelectorAll(".nav-item").forEach((n) => n.classList.remove("active"))

  document.getElementById(sectionId).classList.add("active")
  event.target.closest(".nav-item").classList.add("active")

  // Update header
  const titles = {
    dashboard: ["Dashboard", "Welcome back to your farm"],
    livestock: ["Livestock Management", "Manage all your animals"],
    production: ["Production Records", "Track your farm production"],
    health: ["Health Records", "Manage animal health"],
    finance: ["Finance Management", "Track income and expenses"],
    feeding: ["Feeding Schedule", "Manage feeding plans"],
    profile: ["My Profile", "Manage your account"],
  }

  if (titles[sectionId]) {
    document.getElementById("pageTitle").textContent = titles[sectionId][0]
    document.getElementById("pageSubtitle").textContent = titles[sectionId][1]
  }
}

// Logout
function logout() {
  if (confirm("Are you sure you want to logout?")) {
    fetch("../api/auth.php?action=logout", { method: "POST" }).then(() => (window.location.href = "login.php"))
  }
}

// Load Dashboard Data
async function loadDashboard() {
  try {
    const livestockRes = await fetch("../api/livestock.php?action=list")
    const livestockData = await livestockRes.json()

    if (livestockData.success) {
      document.getElementById("totalLivestock").textContent = livestockData.data.length

      const healthy = livestockData.data.filter((l) => l.health_status === "healthy").length
      const warning = livestockData.data.filter((l) => l.health_status === "warning").length

      document.getElementById("healthyCount").textContent = healthy
      document.getElementById("warningCount").textContent = warning

      loadLivestockTable(livestockData.data)
      populateLivestockDropdown(livestockData.data)
    }
  } catch (error) {
    console.error("[v0] Error loading livestock:", error)
  }

  try {
    const financeRes = await fetch("../api/finance.php?action=summary")
    const financeData = await financeRes.json()

    if (financeData.success) {
      const data = financeData.data
      document.getElementById("monthlyIncome").textContent = "$" + data.monthly_income.toFixed(2)
      document.getElementById("incomeAmount").textContent = "$" + data.monthly_income.toFixed(2)
      document.getElementById("expenseAmount").textContent = "$" + data.monthly_expense.toFixed(2)
      document.getElementById("profitAmount").textContent = "$" + data.profit.toFixed(2)
    }
  } catch (error) {
    console.error("[v0] Error loading finance summary:", error)
  }

  loadProductionData()
  loadHealthData()
}

// Load Livestock Table
function loadLivestockTable(data) {
  const tbody = document.getElementById("livestockTable")

  if (data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No livestock records yet</td></tr>'
    return
  }

  tbody.innerHTML = data
    .map(
      (animal) => `
        <tr>
            <td>${animal.tag_number}</td>
            <td>${animal.animal_type}</td>
            <td>${animal.breed || "-"}</td>
            <td>${animal.gender}</td>
            <td><span class="badge ${animal.health_status}">${animal.health_status}</span></td>
            <td>
                <button class="btn-text" onclick="editLivestock(${animal.id})">Edit</button>
                <button class="btn-text" onclick="deleteLivestock(${animal.id})">Delete</button>
            </td>
        </tr>
    `,
    )
    .join("")

  // Update recent livestock
  const recent = data.slice(0, 3)
  document.getElementById("recentLivestock").innerHTML =
    recent
      .map(
        (animal) => `
        <tr>
            <td>${animal.tag_number}</td>
            <td>${animal.animal_type}</td>
            <td><span class="badge ${animal.health_status}">${animal.health_status}</span></td>
        </tr>
    `,
      )
      .join("") || '<tr><td colspan="3">No records</td></tr>'
}

// Populate Livestock Dropdown
function populateLivestockDropdown(data) {
  const select = document.getElementById("healthLivestock")
  select.innerHTML =
    '<option value="">Select animal</option>' +
    data.map((animal) => `<option value="${animal.id}">${animal.tag_number} (${animal.animal_type})</option>`).join("")
}

// Add Livestock
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("addLivestockForm")
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault()

      const data = {
        animal_type: document.getElementById("animalType").value,
        tag_number: document.getElementById("tagNumber").value,
        breed: document.getElementById("breed").value,
        gender: document.getElementById("gender").value,
        age_months: Number.parseInt(document.getElementById("ageMonths").value) || 0,
        weight: Number.parseFloat(document.getElementById("weight").value) || 0,
      }

      try {
        const response = await fetch("../api/livestock.php?action=add", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        })

        const result = await response.json()
        if (result.success) {
          alert("Livestock added successfully")
          closeModal("addLivestockModal")
          form.reset()
          loadDashboard()
        } else {
          alert(result.message || "Failed to add livestock")
        }
      } catch (error) {
        console.error("[v0] Error:", error)
      }
    })
  }

  // Production Form
  const prodForm = document.getElementById("addProductionForm")
  if (prodForm) {
    prodForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      const data = {
        production_type: document.getElementById("productionType").value,
        quantity: Number.parseFloat(document.getElementById("quantity").value),
        unit: document.getElementById("unit").value,
        production_date: document.getElementById("prodDate").value,
        price_per_unit: Number.parseFloat(document.getElementById("pricePerUnit").value) || 0,
      }

      try {
        const response = await fetch("../api/production.php?action=add", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        })

        const result = await response.json()
        if (result.success) {
          alert("Production record added")
          closeModal("addProductionModal")
          prodForm.reset()
          loadProductionData()
        } else {
          alert(result.message || "Failed to add record")
        }
      } catch (error) {
        console.error("[v0] Error:", error)
      }
    })
  }

  // Health Form
  const healthForm = document.getElementById("addHealthForm")
  if (healthForm) {
    healthForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      const data = {
        livestock_id: Number.parseInt(document.getElementById("healthLivestock").value),
        health_issue: document.getElementById("healthIssue").value,
        treatment: document.getElementById("treatment").value,
        medication: document.getElementById("medication").value,
        treatment_date: document.getElementById("treatmentDate").value,
      }

      try {
        const response = await fetch("../api/health.php?action=add", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        })

        const result = await response.json()
        if (result.success) {
          alert("Health record added")
          closeModal("addHealthModal")
          healthForm.reset()
          loadHealthData()
        } else {
          alert(result.message || "Failed to add record")
        }
      } catch (error) {
        console.error("[v0] Error:", error)
      }
    })
  }

  // Finance Form
  const financeForm = document.getElementById("addFinanceForm")
  if (financeForm) {
    financeForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      const data = {
        transaction_type: document.getElementById("transType").value,
        category: document.getElementById("financeCategory").value,
        amount: Number.parseFloat(document.getElementById("financeAmount").value),
        description: document.getElementById("financeDescription").value,
        transaction_date: document.getElementById("financeDate").value,
      }

      try {
        const response = await fetch("../api/finance.php?action=add", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        })

        const result = await response.json()
        if (result.success) {
          alert("Finance record added")
          closeModal("addFinanceModal")
          financeForm.reset()
          loadDashboard()
        } else {
          alert(result.message || "Failed to add record")
        }
      } catch (error) {
        console.error("[v0] Error:", error)
      }
    })
  }

  loadDashboard()
})

// Load Production Data
async function loadProductionData() {
  try {
    const response = await fetch("../api/production.php?action=list")
    const data = await response.json()

    if (data.success) {
      const tbody = document.getElementById("productionTable")
      tbody.innerHTML =
        data.data
          .map(
            (record) => `
                <tr>
                    <td>${record.production_date}</td>
                    <td>${record.production_type}</td>
                    <td>${record.quantity}</td>
                    <td>${record.unit}</td>
                    <td>$${(record.total_value || 0).toFixed(2)}</td>
                </tr>
            `,
          )
          .join("") || '<tr><td colspan="5">No records</td></tr>'
    }
  } catch (error) {
    console.error("[v0] Error loading production:", error)
  }
}

// Load Health Data
async function loadHealthData() {
  try {
    const response = await fetch("../api/health.php?action=list")
    const data = await response.json()

    if (data.success) {
      const tbody = document.getElementById("healthTable")
      tbody.innerHTML =
        data.data
          .map(
            (record) => `
                <tr>
                    <td>${record.treatment_date}</td>
                    <td>${record.tag_number || "Unknown"}</td>
                    <td>${record.health_issue}</td>
                    <td>${record.treatment}</td>
                    <td>${record.recovery_date ? "Recovered" : "Active"}</td>
                </tr>
            `,
          )
          .join("") || '<tr><td colspan="5">No records</td></tr>'
    }
  } catch (error) {
    console.error("[v0] Error loading health:", error)
  }
}

// Delete Livestock
async function deleteLivestock(id) {
  if (!confirm("Delete this livestock record?")) return

  try {
    const response = await fetch("../api/livestock.php?action=delete", {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id }),
    })

    const result = await response.json()
    if (result.success) {
      loadDashboard()
    }
  } catch (error) {
    console.error("[v0] Error:", error)
  }
}

// Update Profile
async function updateProfile() {
  const data = {
    full_name: document.getElementById("profileName").value,
    phone: document.getElementById("profilePhone").value,
  }

  try {
    const response = await fetch("../api/users.php?action=update_profile", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    })

    const result = await response.json()
    alert(result.message)
  } catch (error) {
    console.error("[v0] Error:", error)
  }
}

// Change Password
async function changePassword() {
  const oldPassword = document.getElementById("oldPassword").value
  const newPassword = document.getElementById("newPassword").value
  const confirmPassword = document.getElementById("confirmPassword").value

  if (newPassword !== confirmPassword) {
    alert("Passwords do not match")
    return
  }

  try {
    const response = await fetch("../api/users.php?action=change_password", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ old_password: oldPassword, new_password: newPassword }),
    })

    const result = await response.json()
    alert(result.message)
    if (result.success) {
      document.getElementById("oldPassword").value = ""
      document.getElementById("newPassword").value = ""
      document.getElementById("confirmPassword").value = ""
    }
  } catch (error) {
    console.error("[v0] Error:", error)
  }
}
