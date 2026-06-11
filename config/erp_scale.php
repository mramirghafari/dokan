<?php

return [
    'slow_query' => [
        'enabled' => env('ERP_SLOW_QUERY_LOG_ENABLED', true),
        'threshold_ms' => (int) env('ERP_SLOW_QUERY_THRESHOLD_MS', 750),
        'channel' => env('ERP_SLOW_QUERY_LOG_CHANNEL', 'slow_query'),
    ],

    'archive' => [
        'enabled' => env('ERP_ARCHIVE_POLICY_ENABLED', true),
        'retention_days' => (int) env('ERP_ARCHIVE_RETENTION_DAYS', 180),
        'chunk_size' => (int) env('ERP_ARCHIVE_CHUNK_SIZE', 500),
        'max_rows_per_table' => (int) env('ERP_ARCHIVE_MAX_ROWS_PER_TABLE', 50000),
        'sources' => [
            'notifs' => [
                'date_column' => 'created_at',
                'mode' => 'purge',
            ],
            'crm_integration_sync_logs' => [
                'date_column' => 'created_at',
                'mode' => 'purge',
            ],
            'bi_report_runs' => [
                'date_column' => 'created_at',
                'mode' => 'purge',
                'only_status' => ['success', 'viewed'],
            ],
        ],
    ],

    'queue' => [
        'heavy_connection' => env('ERP_HEAVY_QUEUE_CONNECTION'),
        'heavy_queue' => env('ERP_HEAVY_QUEUE', 'heavy'),
        'export_row_threshold' => (int) env('ERP_EXPORT_ROW_THRESHOLD', 2000),
    ],

    'crm_campaign' => [
        'sms_enabled' => env('CRM_CAMPAIGN_SMS_ENABLED', false),
        'sms_batch_size' => (int) env('CRM_CAMPAIGN_SMS_BATCH', 25),
        'max_audience_per_dispatch' => (int) env('CRM_CAMPAIGN_MAX_AUDIENCE', 100),
        'sms_dry_run' => env('CRM_CAMPAIGN_SMS_DRY_RUN', true),
        'fixed_messages' => [
            'default' => 'مشتری گرامی {name}، از همراهی شما سپاسگزاریم. تیم فروش {campaign}',
            'acquisition' => 'سلام {name}؛ پیشنهاد ویژه ما را از دست ندهید. {campaign}',
            'retention' => '{name} عزیز، مشتری ارزشمند ما هستید. {campaign}',
            'winback' => '{name}، دلتنگ شما هستیم. برای بازگشت تخفیف ویژه داریم.',
            'upsell' => '{name}، فرصت ارتقای خرید با مزایای بیشتر. {campaign}',
            'loyalty' => '{name}؛ امتیاز باشگاه مشتریان شما در انتظار است.',
        ],
    ],

    'bi_executive' => [
        'comparison_days' => (int) env('BI_EXECUTIVE_COMPARE_DAYS', 7),
    ],

    'bi_report_builder' => [
        'chart_max_points' => (int) env('BI_REPORT_CHART_MAX_POINTS', 30),
        'pivot_max_columns' => (int) env('BI_REPORT_PIVOT_MAX_COLUMNS', 12),
        'export_formats' => ['csv', 'xlsx', 'pdf'],
    ],

    'bi_reconciliation' => [
        'warning_delta_percent' => (float) env('BI_RECON_WARNING_PERCENT', 2),
        'critical_delta_percent' => (float) env('BI_RECON_CRITICAL_PERCENT', 10),
        'default_backfill_months' => (int) env('BI_BACKFILL_DEFAULT_MONTHS', 12),
        'max_backfill_days' => (int) env('BI_BACKFILL_MAX_DAYS', 400),
    ],

    'customer_list_summary' => [
        'cache_ttl' => (int) env('ERP_CUSTOMER_LIST_SUMMARY_TTL', 300),
        'filtered_cache_ttl' => (int) env('ERP_CUSTOMER_LIST_FILTERED_TTL', 60),
    ],

    'remote_lookup' => [
        'minimum_input_length' => (int) env('ERP_REMOTE_LOOKUP_MIN_CHARS', 2),
        'default_limit' => (int) env('ERP_REMOTE_LOOKUP_LIMIT', 20),
        'product_filters' => ['is_active' => 1, 'is_material' => 0],
        'employee_filters' => ['is_active' => 1],
    ],

    'load_test' => [
        'marker' => env('ERP_SCALE_TEST_MARKER', 'STSCALE'),
        'tenant_id' => (int) env('ERP_SCALE_TEST_TENANT_ID', 1),
        'user_id' => env('ERP_SCALE_TEST_USER_ID'),
        'invoice_id_base' => (int) env('ERP_SCALE_TEST_INVOICE_BASE', 900000000),
        'chunk_size' => (int) env('ERP_SCALE_TEST_CHUNK_SIZE', 500),
        'counts' => [
            'customers' => (int) env('ERP_SCALE_TEST_CUSTOMERS', 10000),
            'products' => (int) env('ERP_SCALE_TEST_PRODUCTS', 5000),
            'pishfactors' => (int) env('ERP_SCALE_TEST_PISHFACTORS', 50000),
        ],
        'thresholds' => [
            'default_ms' => (int) env('ERP_LOAD_TEST_DEFAULT_MS', 2000),
            'customer_list_page_1_ms' => (int) env('ERP_LOAD_TEST_CUSTOMER_PAGE_MS', 2000),
            'customer_list_page_50_ms' => (int) env('ERP_LOAD_TEST_CUSTOMER_PAGE_MS', 2000),
            'customer_search_ms' => (int) env('ERP_LOAD_TEST_CUSTOMER_SEARCH_MS', 2000),
            'pishfactor_list_page_1_ms' => (int) env('ERP_LOAD_TEST_PISHFACTOR_PAGE_MS', 2000),
            'bi_refresh_ms' => (int) env('ERP_LOAD_TEST_BI_REFRESH_MS', 15000),
        ],
        'gate' => [
            'scale_score_min' => (int) env('ERP_GATE_SCALE_SCORE_MIN', 85),
            'slow_query_spike_ms' => (int) env('ERP_GATE_SLOW_QUERY_MS', 2000),
        ],
    ],

    'tenant_backfill' => [
        'chunk_size' => (int) env('ERP_TENANT_BACKFILL_CHUNK', 500),
        'tables' => [
            'customers' => [
                'strategies' => [
                    ['name' => 'from_organization'],
                    ['name' => 'from_user_column', 'column' => 'created_by'],
                ],
            ],
            'products' => [
                'strategies' => [
                    ['name' => 'from_organization'],
                    ['name' => 'from_user_column', 'column' => 'user_id'],
                ],
            ],
            'pishfactors' => [
                'strategies' => [
                    ['name' => 'from_customer', 'sync_organization' => true],
                    ['name' => 'from_organization'],
                    ['name' => 'from_user_column', 'column' => 'visitor_id'],
                    ['name' => 'sync_legacy_tenant_column'],
                ],
            ],
            'crm_followups' => [
                'strategies' => [
                    ['name' => 'from_customer', 'sync_organization' => true],
                    ['name' => 'from_customer_organization'],
                ],
            ],
            'crm_opportunities' => [
                'strategies' => [
                    ['name' => 'from_customer', 'sync_organization' => true],
                    ['name' => 'from_customer_organization'],
                ],
            ],
            'crm_service_tickets' => [
                'strategies' => [
                    ['name' => 'from_customer', 'sync_organization' => true],
                    ['name' => 'from_customer_organization'],
                ],
            ],
            'crm_call_logs' => [
                'strategies' => [
                    ['name' => 'from_customer', 'sync_organization' => true],
                ],
            ],
            'crm_leads' => [
                'strategies' => [
                    ['name' => 'from_customer', 'sync_organization' => true],
                ],
            ],
        ],
    ],

    'tenant_scope' => [
        'enabled' => env('ERP_TENANT_SCOPE_ENABLED', true),
        'audit' => [
            'crm_tables' => [
                'crm_followups',
                'crm_opportunities',
                'crm_service_tickets',
                'crm_call_logs',
                'crm_leads',
                'crm_campaigns',
                'crm_campaign_audiences',
                'crm_sales_boards',
                'crm_sales_board_lists',
                'crm_sales_board_cards',
                'crm_automation_rules',
                'crm_integration_sync_logs',
                'crm_collaboration_comments',
            ],
            'erp_core_tables' => [
                'customers',
                'pishfactors',
                'products',
            ],
            'bi_tables' => [
                'bi_daily_summaries',
                'bi_report_runs',
                'bi_refresh_logs',
                'bi_insight_alerts',
            ],
        ],
    ],
];
