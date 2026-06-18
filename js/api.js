// API Service for XAMPP backend
const API = {
    baseUrl: '/api',
    
    // Auth
    async login(email, password) {
        const response = await fetch(`${this.baseUrl}/auth/login.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        return response.json();
    },
    
    async logout() {
        const response = await fetch(`${this.baseUrl}/auth/logout.php`);
        return response.json();
    },
    
    // Projects
    async getProjects(filters = {}) {
        const params = new URLSearchParams(filters);
        const response = await fetch(`${this.baseUrl}/projects/list.php?${params}`);
        return response.json();
    },
    
    async createProject(data) {
        const response = await fetch(`${this.baseUrl}/projects/create.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return response.json();
    },
    
    async updateProject(id, data) {
        const response = await fetch(`${this.baseUrl}/projects/update.php`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, ...data })
        });
        return response.json();
    },
    
    async deleteProject(id) {
        const response = await fetch(`${this.baseUrl}/projects/delete.php?id=${id}`, {
            method: 'DELETE'
        });
        return response.json();
    },
    
    // Analytics
    async getPortfolioStats() {
        const response = await fetch(`${this.baseUrl}/analytics/portfolio.php`);
        return response.json();
    },
    
    async getBusinessUnitStats(unitId) {
        const response = await fetch(`${this.baseUrl}/analytics/business-unit.php?id=${unitId}`);
        return response.json();
    },
    
    // Governance
    async updateGovernance(projectId, itemKey, status) {
        const response = await fetch(`${this.baseUrl}/governance/update.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ project_id: projectId, item_key: itemKey, status })
        });
        return response.json();
    },
    
    // Import/Export
    async importExcel(file) {
        const formData = new FormData();
        formData.append('file', file);
        const response = await fetch(`${this.baseUrl}/import/excel.php`, {
            method: 'POST',
            body: formData
        });
        return response.json();
    },
    
    async exportCSV(filters = {}) {
        const params = new URLSearchParams(filters);
        window.open(`${this.baseUrl}/export/csv.php?${params}`);
    }
};

// Session management
let currentUser = null;

async function checkAuth() {
    const response = await fetch('/api/auth/check.php');
    const data = await response.json();
    if (data.user) {
        currentUser = data.user;
        return true;
    }
    return false;
}

// Show login modal if not authenticated
async function requireAuth() {
    const isAuth = await checkAuth();
    if (!isAuth) {
        showLoginModal();
        return false;
    }
    return true;
}