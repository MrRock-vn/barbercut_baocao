<?php
/**
 * LOPAS Voucher Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Voucher_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_voucher_page'));
        add_action('wp_ajax_lopas_create_voucher', array($this, 'ajax_create_voucher'));
        add_action('wp_ajax_lopas_update_voucher', array($this, 'ajax_update_voucher'));
        add_action('wp_ajax_lopas_delete_voucher', array($this, 'ajax_delete_voucher'));
    }
    
    /**
     * Add voucher page to admin menu
     */
    public function add_voucher_page() {
        add_submenu_page(
            'lopas-dashboard',
            'Vouchers',
            'Vouchers',
            'manage_options',
            'lopas-vouchers',
            array($this, 'render_voucher_page')
        );
    }
    
    /**
     * Render voucher page
     */
    public function render_voucher_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        require_once LOPAS_PATH . 'includes/class-voucher.php';
        $vouchers = LOPAS_Voucher::get_all(50, 0);
        
        ?>
        <div class="wrap">
            <h1>Vouchers</h1>
            
            <button class="button button-primary" id="btn-add-voucher">Add New Voucher</button>
            
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Valid From</th>
                        <th>Valid Until</th>
                        <th>Uses</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vouchers as $voucher): ?>
                    <tr>
                        <td><strong><?php echo esc_html($voucher->code); ?></strong></td>
                        <td><?php echo esc_html(ucfirst($voucher->discount_type)); ?></td>
                        <td>
                            <?php 
                            if ($voucher->discount_type === 'percentage') {
                                echo esc_html($voucher->discount_value) . '%';
                            } else {
                                echo number_format($voucher->discount_value, 0, ',', '.') . ' VND';
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html($voucher->valid_from); ?></td>
                        <td><?php echo esc_html($voucher->valid_until); ?></td>
                        <td><?php echo esc_html($voucher->current_uses); ?> / <?php echo $voucher->max_uses > 0 ? esc_html($voucher->max_uses) : 'Unlimited'; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($voucher->status); ?>">
                                <?php echo esc_html(ucfirst($voucher->status)); ?>
                            </span>
                        </td>
                        <td>
                            <button class="button button-small btn-edit-voucher" data-id="<?php echo esc_attr($voucher->id); ?>">Edit</button>
                            <button class="button button-small button-link-delete btn-delete-voucher" data-id="<?php echo esc_attr($voucher->id); ?>">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Add/Edit Modal -->
            <div id="voucher-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 id="modal-title">Add New Voucher</h2>
                    
                    <form id="voucher-form">
                        <?php wp_nonce_field('lopas_nonce', 'nonce'); ?>
                        <input type="hidden" id="voucher-id" name="voucher_id" value="">
                        
                        <div class="form-group">
                            <label for="voucher-code">Voucher Code:</label>
                            <input type="text" id="voucher-code" name="code" placeholder="e.g., SUMMER20" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="discount-type">Discount Type:</label>
                            <select id="discount-type" name="discount_type" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (VND)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="discount-value">Discount Value:</label>
                            <input type="number" id="discount-value" name="discount_value" placeholder="e.g., 20" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="max-uses">Max Uses (0 = Unlimited):</label>
                            <input type="number" id="max-uses" name="max_uses" placeholder="0" min="0" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="valid-from">Valid From:</label>
                            <input type="date" id="valid-from" name="valid_from" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="valid-until">Valid Until:</label>
                            <input type="date" id="valid-until" name="valid_until" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="voucher-status">Status:</label>
                            <select id="voucher-status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="button button-primary">Save Voucher</button>
                    </form>
                </div>
            </div>
            
            <style>
                .modal {
                    position: fixed;
                    z-index: 1000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.4);
                }
                
                .modal-content {
                    background-color: #fefefe;
                    margin: 5% auto;
                    padding: 20px;
                    border: 1px solid #888;
                    width: 500px;
                    border-radius: 5px;
                }
                
                .close {
                    color: #aaa;
                    float: right;
                    font-size: 28px;
                    font-weight: bold;
                    cursor: pointer;
                }
                
                .close:hover {
                    color: black;
                }
                
                .form-group {
                    margin: 15px 0;
                }
                
                .form-group label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: bold;
                }
                
                .form-group input,
                .form-group select {
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                    box-sizing: border-box;
                }
                
                .status-badge {
                    padding: 5px 10px;
                    border-radius: 3px;
                    font-weight: bold;
                }
                
                .status-active {
                    background: #d4edda;
                    color: #155724;
                }
                
                .status-inactive {
                    background: #f8d7da;
                    color: #721c24;
                }
                
                .widefat {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                
                .widefat th,
                .widefat td {
                    padding: 12px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                
                .widefat th {
                    background: #f5f5f5;
                    font-weight: bold;
                }
                
                .widefat tbody tr:hover {
                    background: #f9f9f9;
                }
            </style>
            
            <script>
                const modal = document.getElementById('voucher-modal');
                const closeBtn = document.querySelector('.close');
                const addBtn = document.getElementById('btn-add-voucher');
                const form = document.getElementById('voucher-form');
                
                addBtn.addEventListener('click', function() {
                    document.getElementById('modal-title').textContent = 'Add New Voucher';
                    form.reset();
                    document.getElementById('voucher-id').value = '';
                    document.getElementById('valid-from').valueAsDate = new Date();
                    document.getElementById('valid-until').valueAsDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000);
                    modal.style.display = 'block';
                });
                
                closeBtn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
                
                window.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
                
                document.querySelectorAll('.btn-edit-voucher').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const voucherId = this.dataset.id;
                        // Load voucher data and populate form
                        document.getElementById('modal-title').textContent = 'Edit Voucher';
                        document.getElementById('voucher-id').value = voucherId;
                        modal.style.display = 'block';
                    });
                });
                
                document.querySelectorAll('.btn-delete-voucher').forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (confirm('Are you sure you want to delete this voucher?')) {
                            const voucherId = this.dataset.id;
                            
                            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `action=lopas_delete_voucher&voucher_id=${voucherId}&nonce=<?php echo wp_create_nonce('lopas_nonce'); ?>`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    location.reload();
                                } else {
                                    alert('Error: ' + data.data.message);
                                }
                            });
                        }
                    });
                });
                
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const voucherId = document.getElementById('voucher-id').value;
                    const action = voucherId ? 'lopas_update_voucher' : 'lopas_create_voucher';
                    const formData = new FormData(this);
                    formData.append('action', action);
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.data.message);
                        }
                    });
                });
            </script>
        </div>
        <?php
    }
    
    /**
     * AJAX: Create voucher
     */
    public function ajax_create_voucher() {
        check_ajax_referer('lopas_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        require_once LOPAS_PATH . 'includes/class-voucher.php';
        
        $voucher_id = LOPAS_Voucher::create(array(
            'code' => sanitize_text_field($_POST['code'] ?? ''),
            'discount_type' => sanitize_text_field($_POST['discount_type'] ?? 'percentage'),
            'discount_value' => floatval($_POST['discount_value'] ?? 0),
            'max_uses' => intval($_POST['max_uses'] ?? 0),
            'valid_from' => sanitize_text_field($_POST['valid_from'] ?? ''),
            'valid_until' => sanitize_text_field($_POST['valid_until'] ?? ''),
            'status' => sanitize_text_field($_POST['status'] ?? 'active')
        ));
        
        if ($voucher_id) {
            wp_send_json_success(array('voucher_id' => $voucher_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to create voucher'));
        }
    }
    
    /**
     * AJAX: Update voucher
     */
    public function ajax_update_voucher() {
        check_ajax_referer('lopas_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        require_once LOPAS_PATH . 'includes/class-voucher.php';
        
        $voucher_id = intval($_POST['voucher_id'] ?? 0);
        
        $result = LOPAS_Voucher::update($voucher_id, array(
            'code' => sanitize_text_field($_POST['code'] ?? ''),
            'discount_type' => sanitize_text_field($_POST['discount_type'] ?? 'percentage'),
            'discount_value' => floatval($_POST['discount_value'] ?? 0),
            'max_uses' => intval($_POST['max_uses'] ?? 0),
            'valid_from' => sanitize_text_field($_POST['valid_from'] ?? ''),
            'valid_until' => sanitize_text_field($_POST['valid_until'] ?? ''),
            'status' => sanitize_text_field($_POST['status'] ?? 'active')
        ));
        
        if ($result) {
            wp_send_json_success(array('message' => 'Voucher updated'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update voucher'));
        }
    }
    
    /**
     * AJAX: Delete voucher
     */
    public function ajax_delete_voucher() {
        check_ajax_referer('lopas_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        require_once LOPAS_PATH . 'includes/class-voucher.php';
        
        $voucher_id = intval($_POST['voucher_id'] ?? 0);
        
        if (LOPAS_Voucher::delete($voucher_id)) {
            wp_send_json_success(array('message' => 'Voucher deleted'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete voucher'));
        }
    }
}
