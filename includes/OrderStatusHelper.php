<?php
/**
 * Shared Order Status Helper
 *
 * Provides a single source of truth for order/PO status classification
 * and corresponding color mappings used across:
 *  - /v3/ColdStore/Ajax/ajax-po-status-reports.php
 *  - /v3/ColdStore/Ajax/ajax-dashboard-insights.php
 *  - /v3/ColdStore/index.php
 *
 * Status Rules:
 *   Completed = packedQty >= orderQty AND orderQty > 0
 *   Pending   = packedQty = 0 OR orderQty = 0
 *   Partial   = 0 < packedQty < orderQty
 *
 * Color Mapping:
 *   Green  (#22c55e) badge-success  = Completed
 *   Yellow (#f59e0b) badge-warning  = Partial
 *   Red    (#ef4444) badge-danger   = Pending
 */

if (!function_exists('getOrderStatusSqlCase')) {
    /**
     * Returns the SQL CASE expression for determining order status.
     *
     * @param string $avalExpr  SQL expression for available/packed quantity
     * @param string $orderExpr SQL expression for ordered quantity
     * @return string SQL CASE expression fragment
     */
    function getOrderStatusSqlCase($avalExpr, $orderExpr)
    {
        return "CASE
            WHEN ($avalExpr >= $orderExpr AND $orderExpr > 0) THEN 'Completed'
            WHEN (COALESCE($avalExpr, 0) = 0 OR $orderExpr = 0) THEN 'Pending'
            ELSE 'Partial'
        END";
    }
}

if (!function_exists('getOrderStatusLabel')) {
    /**
     * PHP-side status label based on packed and order quantities.
     *
     * @param float $packedQty
     * @param float $orderQty
     * @return string 'Completed', 'Pending', or 'Partial'
     */
    function getOrderStatusLabel($packedQty, $orderQty)
    {
        $packedQty = (float)$packedQty;
        $orderQty  = (float)$orderQty;

        if ($packedQty >= $orderQty && $orderQty > 0) {
            return 'Completed';
        }
        if ($packedQty == 0 || $orderQty == 0) {
            return 'Pending';
        }
        return 'Partial';
    }
}

if (!function_exists('getOrderStatusBadgeClass')) {
    /**
     * Returns the Bootstrap badge CSS class for a given status label.
     *
     * @param string $status 'Completed', 'Pending', or 'Partial'
     * @return string CSS class name (e.g. 'badge-success')
     */
    function getOrderStatusBadgeClass($status)
    {
        switch ($status) {
            case 'Completed': return 'badge-success';
            case 'Pending':   return 'badge-danger';
            case 'Partial':   return 'badge-warning';
            default:          return 'badge-secondary';
        }
    }
}

if (!function_exists('getOrderStatusColor')) {
    /**
     * Returns the hex color for a given status label.
     *
     * @param string $status 'Completed', 'Pending', or 'Partial'
     * @return string hex color code
     */
    function getOrderStatusColor($status)
    {
        switch ($status) {
            case 'Completed': return '#22c55e';
            case 'Pending':   return '#ef4444';
            case 'Partial':   return '#f59e0b';
            default:          return '#6b7280';
        }
    }
}
