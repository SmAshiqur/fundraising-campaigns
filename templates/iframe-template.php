<?php
/**
 * Template for displaying campaigns in an iframe.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Fundraising_Campaigns
 * @subpackage WP_Fundraising_Campaigns/public/partials
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Generate unique ID for this instance
$instance_id = 'wpfc-' . uniqid();





// Get company from URL parameter if provided
$url_company = isset($_GET['company']) ? sanitize_text_field($_GET['company']) : '';

// Add this near the top of the file
$settings = get_option('wpfc_settings', array());

// Use admin panel company if set, otherwise use URL parameter
$company_name = !empty($settings['company_name']) ? $settings['company_name'] : $url_company;

// Add these before the CSS section
$primary_color = isset($settings['primary_color']) ? $settings['primary_color'] : '#3498db';
$secondary_color = isset($settings['secondary_color']) ? $settings['secondary_color'] : '#2980b9';

// Output custom CSS with dynamic colors
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fundraising Campaigns</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        
        .wpfc-container-<?php echo esc_attr($instance_id); ?> {
            --wpfc-primary-color: <?php echo esc_attr($primary_color); ?>;
            --wpfc-secondary-color: <?php echo esc_attr($secondary_color); ?>;
            --wpfc-accent-color: #e74c3c;
            --wpfc-light-color: #f9f9f9;
            --wpfc-dark-color: #333;
            --wpfc-text-color: #4a4a4a;
            --wpfc-border-radius: 8px;
            --wpfc-box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 15px;
        }
        
        /* Equal height grid setup */
        .wpfc-campaigns-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .wpfc-campaign-card-wrapper {
            height: 100%;
        }
        
        /* Campaign card link styles */
        .wpfc-campaign-card-link {
            display: block;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            height: 100%;
        }
        
        .wpfc-campaign-card-link:hover {
            transform: translateY(-3px);
            box-shadow: var(--wpfc-box-shadow);
        }
        
        /* Set fixed heights and make card flex container */
        .wpfc-campaign-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            border: 1px solid #e0e0e0;
            border-radius: var(--wpfc-border-radius);
            overflow: hidden;
            background-color: #fff;
        }
        
        .wpfc-campaign-image {
            position: relative;
            height: 200px; /* Fixed height for images */
            overflow: hidden;
        }
        
        .wpfc-campaign-image img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures images maintain aspect ratio */
        }
        
        .wpfc-company-logo {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            background-color: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        
        .wpfc-company-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .wpfc-campaign-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 15px;
        }
        
        .wpfc-campaign-title {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 18px;
            line-height: 1.3;
        }
        
        .wpfc-campaign-details {
            flex: 1; /* Allow this section to grow */
            overflow: hidden;
        }
        
        .wpfc-campaign-details p {
            display: -webkit-box;
            -webkit-line-clamp: 3; /* Limit to 3 lines */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 14px;
            line-height: 1.5;
            color: var(--wpfc-text-color);
        }
        
        .wpfc-donation-count {
            font-size: 14px;
            color: #777;
            margin-bottom: 10px;
        }
        
        .wpfc-progress-container {
            margin-bottom: 15px;
        }
        
        .wpfc-progress-bar {
            height: 8px;
            background-color: #f1f1f1;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .wpfc-progress-fill {
            height: 100%;
            background-color: var(--wpfc-primary-color);
        }
        
        /* Footer elements */
        .wpfc-campaign-footer {
            margin-top: auto; /* Push to bottom of card */
            display: flex;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 14px;
        }
        
        .wpfc-campaign-donation span,
        .wpfc-campaign-percentage span {
            font-weight: bold;
            color: var(--wpfc-primary-color);
        }
        
        /* Hover overlay and donate button styles */
        .wpfc-campaign-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: var(--wpfc-border-radius);
            z-index: 5;
        }
        
        .wpfc-campaign-card-link:hover .wpfc-campaign-overlay {
            opacity: 1;
        }
        
        .wpfc-donate-button {
            background-color: #00a63e;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .wpfc-donate-button:hover {
            background-color: rgb(5, 116, 45); /* Darker version of accent color */
        }
        
        /* Loading indicator */
        .wpfc-loading {
            text-align: center;
            padding: 40px;
        }
        
        .wpfc-loading-spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid var(--wpfc-primary-color);
            width: 40px;
            height: 40px;
            animation: wpfc-spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes wpfc-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .wpfc-campaigns-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 480px) {
            .wpfc-campaigns-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="wpfc-container wpfc-container-<?php echo esc_attr($instance_id); ?>">
    <!-- Header section removed as requested for iframe version -->
    
    <div id="wpfc-campaigns-container" class="wpfc-campaigns-container">
        <div class="wpfc-loading">
            <div class="wpfc-loading-spinner"></div>
            <p>Loading campaigns...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get company name from settings or URL parameter
        const companyName = '<?php echo esc_js($company_name); ?>';
        
        if (companyName) {
            fetchCampaigns(companyName);
        } else {
            document.getElementById('wpfc-campaigns-container').innerHTML = 
                '<div style="text-align:center;padding:30px;">No company specified. Please provide a company name in settings or URL parameter.</div>';
        }
    });
    
    function fetchCampaigns(companyName) {
        console.log("Fetching campaigns for company:", companyName);
        
        // Fix the API URL format
        const apiUrl = `https://www.secure-api.net/api/v1/fundraiser-campaign/${companyName}`;
        console.log("API URL:", apiUrl);
        
        fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("API response:", data);
                displayCampaigns(data.campaigns || []);
            })
            .catch(error => {
                console.error('Error fetching campaigns:', error);
                document.getElementById('wpfc-campaigns-container').innerHTML = 
                    `<div style="text-align:center;padding:30px;">Error loading campaigns: ${error.message}</div>`;
            });
    }
    
    function displayCampaigns(campaigns) {
        const container = document.getElementById('wpfc-campaigns-container');
        
        if (campaigns.length === 0) {
            container.innerHTML = '<div style="text-align:center;padding:30px;">No campaigns found for this company.</div>';
            return;
        }
        
        let html = '';
        
        campaigns.forEach(campaign => {
            // Campaign data extraction
            const campaignTitle = campaign.title || '';
            const campaignDescription = campaign.details || '';
            const campaignImage = campaign.defaultImage || '';
            const companyLogo = campaign.companyLogo || '';
            
            // URL construction
            const companySlug = campaign.companySlug || '';
            const campaignSlug = campaign.slug || '';
            const destinationUrl = `https://fundraiser.secure-api.net/${companySlug}/${campaignSlug}`;
            
            // Financial data
            const currentAmount = parseFloat(campaign.totalDonation || 0);
            const targetAmount = parseFloat(campaign.goalAmount || 0);
            const donorCount = parseInt(campaign.donationCount || 0);
            
            // Calculate progress percentage
            const showProgress = (targetAmount > 0);
            const percentage = showProgress ? Math.min(100, (currentAmount / targetAmount * 100)) : 0;
            const percentageFormatted = percentage > 0 ? percentage.toFixed(2) : '0';
            
            // Format currency
            const currentFormatted = new Intl.NumberFormat().format(currentAmount);
            
            html += `
                <div class="wpfc-campaign-card-wrapper">
                    <a href="${destinationUrl}" class="wpfc-campaign-card-link" target="_blank">
                        <div class="wpfc-campaign-overlay">
                            <button class="wpfc-donate-button">Donate Now</button>
                        </div>
                        
                        <div class="wpfc-campaign-card">
                            <div class="wpfc-campaign-image">
                                ${campaignImage ? `<img src="${campaignImage}" alt="${campaignTitle}">` : '<div class="wpfc-placeholder-image"></div>'}
                                
                                ${companyLogo ? `<div class="wpfc-company-logo">
                                    <img src="${companyLogo}" alt="Company Logo">
                                </div>` : ''}
                            </div>
                            
                            <div class="wpfc-campaign-body">
                                <h3 class="wpfc-campaign-title">${campaignTitle}</h3>
                                
                                <div class="wpfc-campaign-details">
                                    <p>${campaignDescription}</p>
                                </div>
                                
                                ${targetAmount > 0 ? `<div class="wpfc-donation-count">
                                    ${donorCount} ${donorCount === 1 ? 'donor' : 'donations'}
                                </div>` : ''}
                                
                                ${showProgress ? `<div class="wpfc-progress-container">
                                    <div class="wpfc-progress-bar">
                                        <div class="wpfc-progress-fill" style="width: ${percentage}%;"></div>
                                    </div>
                                </div>` : ''}
                                
                                <div class="wpfc-campaign-footer">
                                    ${targetAmount > 0 ? `<div class="wpfc-campaign-donation">
                                        <span>$${currentFormatted}</span> raised
                                    </div>
                                    
                                    ${showProgress ? `<div class="wpfc-campaign-percentage">
                                        <span>${percentageFormatted}%</span> funded
                                    </div>` : ''}` : ''}
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
</script>

</body>
</html>




