<?php
/**
 * Template for displaying campaigns.
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

// Output custom CSS with dynamic colors
?>
<style>
    .wpfc-container-<?php echo esc_attr($instance_id); ?> {
        --wpfc-primary-color: <?php echo esc_attr($primary_color); ?>;
        --wpfc-secondary-color: <?php echo esc_attr($secondary_color); ?>;
        --wpfc-accent-color: #e74c3c;
        --wpfc-light-color: #f9f9f9;
        --wpfc-dark-color: #333;
        --wpfc-text-color: #4a4a4a;
        --wpfc-border-radius: 8px;
        --wpfc-box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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
        background-color:rgb(5, 116, 45); /* Darker version of accent color */
    }
    
    /* Make sure the overlay doesn't interfere with image positioning */
    .wpfc-campaign-image {
        position: relative;
    }
</style>

<div class="wpfc-container wpfc-container-<?php echo esc_attr($instance_id); ?>">
    <!-- <header class="wpfc-campaign-header">
        <h2 class="wpfc-title"><?php //echo esc_html($title); ?></h2>
        <p class="wpfc-description"><?php //echo wp_kses_post($description); ?></p>
    </header> -->
    
    <p class="wpfc-all-camp">All Campaigns</p>

    <div class="wpfc-campaigns-container">
        
        <?php foreach ($campaigns as $campaign) : ?>
            
            <?php
            // Extract campaign data with the correct field names
            $campaign_title = isset($campaign['title']) ? $campaign['title'] : '';
            $campaign_description = isset($campaign['details']) ? $campaign['details'] : '';
            $campaign_image = isset($campaign['defaultImage']) ? $campaign['defaultImage'] : '';
            $company_logo = isset($campaign['companyLogo']) ? $campaign['companyLogo'] : '';
            $video_url = isset($campaign['videoLink']) ? $campaign['videoLink'] : '';
            
            // Extract company slug and campaign slug for URL
            $company_slug = isset($campaign['companySlug']) ? $campaign['companySlug'] : '';
            $campaign_slug = isset($campaign['slug']) ? $campaign['slug'] : '';
            
            // Build the destination URL - using current protocol and host
            $base_url = 'https://fundraiser.secure-api.net/';
            $destination_url = $base_url . $company_slug . '/' . $campaign_slug;
            
            // Financials
            $current_amount = isset($campaign['totalDonation']) ? floatval($campaign['totalDonation']) : 0;
            $target_amount = isset($campaign['goalAmount']) ? floatval($campaign['goalAmount']) : 0;
            $target_amount = isset($campaign['goalAmount']) ? floatval($campaign['goalAmount']) : 0;

            if ($target_amount >= 1000) {
                $formatted_target = number_format($target_amount / 1000, ($target_amount % 1000 === 0 ? 0 : 1)) . 'k';
            } else {
                $formatted_target = number_format($target_amount);
            }

            $donor_count = isset($campaign['donationCount']) ? intval($campaign['donationCount']) : 0;
            $pledges = isset($campaign['totalPledge']) ? floatval($campaign['totalPledge']) : 0;
            
            // Check if goal amount is greater than 0 to determine if we should show progress
            $show_progress = ($target_amount > 0);
            
            // Calculate percentage only if we're showing progress
            $percentage = $show_progress ? min(100, ($current_amount / $target_amount) * 100) : 0;

            // Format percentage with decimals only if greater than 0
            if ($percentage > 0) {
                $percentage_formatted = number_format($percentage, 2);
            } else {
                $percentage_formatted = '0';
            }
                    
            // Format amounts
            $current_formatted = number_format($current_amount);
            $target_formatted = number_format($target_amount);
            
            // Status badge - only if showing progress
            $status = $show_progress ? ($percentage >= 100 ? 'funded' : ($percentage >= 50 ? 'half-funded' : 'partial-funding')) : '';
            $status_text = $show_progress ? ($percentage >= 100 ? 'Fully Funded' : ($percentage >= 50 ? 'Half Funded' : 'Partial Funding')) : '';
            
            // Start date - use createdOn instead if available
            $start_date = isset($campaign['createdOn']) ? date_i18n(get_option('date_format'), strtotime($campaign['createdOn'])) : '';
            ?>
            
            <div class="wpfc-campaign-card-wrapper">
                <!-- Card content -->
                <a href="<?php echo esc_url($destination_url); ?>" class="wpfc-campaign-card-link" target="_blank">
                    <!-- Overlay with donate button that appears on hover -->
                    <div class="wpfc-campaign-overlay">
                        <button class="wpfc-donate-button">Donate Now</button>
                    </div>
                    
                    <div class="wpfc-campaign-card">
                        <div class="wpfc-campaign-image">
                            <?php if (!empty($campaign_image)) : ?>
                                <img src="<?php echo esc_url($campaign_image); ?>" alt="<?php echo esc_attr($campaign_title); ?>">
                            <?php else : ?>
                                <div class="wpfc-placeholder-image"></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($company_logo)) : ?>
                                <div class="wpfc-company-logo">
                                    <img src="<?php echo esc_url($company_logo); ?>" alt="Company Logo">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="wpfc-campaign-body">
                            <h3 class="wpfc-campaign-title"><?php echo esc_html($campaign_title); ?></h3>
                            
                            <div class="wpfc-campaign-details">
                                <p><?php echo wp_kses_post($campaign_description); ?></p>
                            </div> 
                            <?php if ($target_amount > 0) : ?>
                                <!-- <div class="wpfc-donation-count">
                                    <?php //echo esc_html($donor_count); ?> <?php //echo esc_html(_n('donor', 'donations', $donor_count, 'wpfc')); ?>                       
                                </div> -->
                            <?php endif; ?>
                            <?php if ($show_progress) : ?>
                            <div class="wpfc-progress-container">
                                <div class="wpfc-progress-bar">
                                    <div class="wpfc-progress-fill" style="width: <?php echo esc_attr($percentage); ?>%;"></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="wpfc-campaign-footer">
                                <?php if ($target_amount > 0) : ?>
                                    <div class="wpfc-campaign-donation">
                                    <span  style="font-weight: bold; font-size: 18px;">$<?php echo esc_html(number_format($current_amount)); ?></span><span> <?php esc_html_e('raised', 'wpfc'); ?>  </span>  <span>   </span> <?php esc_html_e('of ', 'wpfc'); ?> <span><?php echo esc_html($formatted_target); ?></span>

                                    </div>
                                    <?php if ($show_progress) : ?>
                                        <!-- <div class="wpfc-campaign-percentage">
                                        <?php //esc_html_e('Goal ', 'wpfc'); ?> <span><?php //echo esc_html($formatted_target); ?></span>
                                        </div> -->
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>