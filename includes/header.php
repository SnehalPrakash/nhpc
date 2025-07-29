<?php
if (!defined('HEADER_LOADED')) {
    define('HEADER_LOADED', true);
?>
<div class="header-container">
    <div class="header-content">
        <div class="logo-title-wrap">
            <img src="/cws/logo.jpeg" alt="NHPC Logo" class="logo-image">
            <span class="header-title">NHPC EMPANELLED HOSPITALS</span>
        </div>
        <div class="user-profile">
                <img src="/cws/certify.jpeg" alt="User" class="user-icon">
        </div>
    </div>
</div>
<style>
.header-container {
    width: 100%;
    background: linear-gradient(135deg, #004d99, #0066cc);
    padding: 0.5rem 1rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}


.logo-title-wrap {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.header-title {
    color: white;
    font-size: 1.8rem;
    font-weight: bold;
    text-align: left;
    margin-bottom: 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    letter-spacing: 1px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: 'Montserrat', 'Segoe UI', 'Arial', sans-serif;
}

.logo-container {
    flex: 0 0 auto;
}

.logo-image {
    width: 180px;
    height: auto;
    max-height: 90px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.1);
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
}

.user-icon {
    width: 50px;
    height: 56px;
}

.username {
    color: white;
    font-weight: 500;
}
.user-icon:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

@media (max-width: 600px) {
    .header-container {
        padding: 0.5rem;
        margin-bottom: 1rem;
    }
    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    .logo-title-wrap {
        gap: 0.5rem;
    }
    .header-title {
        font-size: 1.2rem;
        text-align: left;
    }
    .logo-image {
        width: 120px;
        max-height: 60px;
    }
    .user-profile {
        padding: 0.3rem 0.7rem;
        border-radius: 15px;
    }
    .user-icon {
        width: 36px;
        height: 40px;
    }
}
</style>
<?php
}
?>