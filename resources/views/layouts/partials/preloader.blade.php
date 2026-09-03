<!-- DiscussHub Dynamic Dual-Ring Preloader -->
<div id="dh-preloader" class="dh-preloader-overlay" aria-label="Loading content">
    <div class="dh-preloader-card">
        <div class="dh-spinner-container">
            <!-- Outer Spinner Ring (Orange Arc Glow) -->
            <div class="dh-spinner-ring dh-spinner-outer"></div>
            
            <!-- Inner Spinner Ring (Blue Arc Glow) -->
            <div class="dh-spinner-ring dh-spinner-inner"></div>
            
            <!-- Centered Favicon Icon -->
            <div class="dh-favicon-wrapper">
                <img src="{{ asset('favicon.png') }}" alt="DiscussHub Favicon" class="dh-favicon-img">
            </div>
        </div>
        
        <!-- Loading Text with Letter & Dot Motion -->
        <div class="dh-loading-text" aria-live="polite">
            <span class="dh-text-chars">LOADING</span>
            <span class="dh-dots">
                <span class="dh-dot">.</span><span class="dh-dot">.</span><span class="dh-dot">.</span>
            </span>
        </div>
    </div>
</div>
