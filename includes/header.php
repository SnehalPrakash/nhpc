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
    padding: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}


.logo-title-wrap {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.header-title {
    color: white;
    font-size: 2.2rem;
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
}

.logo-container {
    flex: 0 0 auto;
}

.logo-image {
    width: 220px;
    height: auto;
    max-height: 120px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.1);
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.user-icon {
    width: 60px;
    height: 68px;
}

.username {
    color: white;
    font-weight: 500;
}
</style>
<?php
}
?>