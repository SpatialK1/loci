<?php
// English (en)
// This is the source language file. All other language files should mirror these keys.
return [
    // General
    'app_name'              => 'Loci',
    'save'                  => 'Save',
    'cancel'                => 'Cancel',
    'delete'                => 'Delete',
    'edit'                  => 'Edit',
    'add'                   => 'Add',
    'close'                 => 'Close',
    'confirm'               => 'Confirm',
    'back'                  => 'Back',
    'next'                  => 'Next',
    'done'                  => 'Done',
    'loading'               => 'Loading…',
    'error'                 => 'An error occurred. Please try again.',
    'success'               => 'Success',
    'required'              => 'Required',
    'optional'              => 'Optional',
    'search'                => 'Search',
    'filter'                => 'Filter',
    'sort'                  => 'Sort',
    'yes'                   => 'Yes',
    'no'                    => 'No',

    // Navigation
    'nav_media'             => 'Media',
    'nav_lists'             => 'Lists',
    'nav_settings'          => 'Settings',
    'nav_import'            => 'Import',
    'nav_logout'            => 'Log Out',

    // Auth
    'login_title'           => 'Log In',
    'login_username'        => 'Username',
    'login_password'        => 'Password',
    'login_submit'          => 'Log In',
    'login_error'           => 'Invalid username or password',
    'login_generic_error'   => 'An error occurred. Please try again.',
    'logout_confirm'        => 'Are you sure you want to log out?',

    // Media
    'media_title'           => 'Media',
    'media_add'             => '+ Add',
    'media_empty'           => 'No items found.',
    'media_delete_confirm'  => 'Delete this item?',
    'media_mark_consumed'   => 'Mark Consumed',
    'media_mark_queue'      => 'Mark Queue',
    'media_add_title'       => 'Add Media',
    'media_edit_title'      => 'Edit Media',

    // Media fields
    'field_type'            => 'Type',
    'field_title'           => 'Title',
    'field_author'          => 'Author',
    'field_url'             => 'URL',
    'field_notes'           => 'Notes',
    'field_recommender'     => 'Recommended by',
    'field_tags'            => 'Tags',
    'field_tags_hint'       => 'Comma separated',
    'field_status'          => 'Status',
    'field_isbn'            => 'ISBN',
    'field_show_name'       => 'Show Name',
    'field_is_dead'         => 'Dead Link',
    'field_is_paywalled'    => 'Paywalled',
    'field_consumed_at'     => 'Date Consumed',
    'field_created_at'      => 'Date Added',
    'field_book_format'     => 'Format',

    // Media types
    'type_url'              => 'URL',
    'type_book'             => 'Book',
    'type_movie'            => 'Movie',
    'type_podcast'          => 'Podcast',

    // Media status
    'status_queue'          => 'Queue',
    'status_consumed'       => 'Consumed',

    // Media visibility
    'field_visibility'   => 'Visibility',
    'visibility_private' => 'Private',
    'visibility_group'   => 'Group',
    'visibility_public'  => 'Public',

    // Book formats
    'format_paperback'      => 'Paperback',
    'format_hardcover'      => 'Hardcover',
    'format_ebook'          => 'Ebook',

    // Filters
    'filter_all_types'      => 'All Types',
    'filter_all_status'     => 'All',
    'filter_all_recommenders' => 'All Recommenders',
    'filter_by_tag'         => 'Filter by tag',

    // Sort
    'sort_date_added'       => 'Date Added',
    'sort_title'            => 'Title',
    'sort_type'             => 'Type',
    'sort_status'           => 'Status',
    'sort_recommender'      => 'Recommender',
    'sort_show_name'        => 'Show Name',
    'sort_newest'           => 'Newest First',
    'sort_oldest'           => 'Oldest First',

    // View modes
    'view_list'             => 'List',
    'view_card'             => 'Card',

    // Lists
    'lists_title'           => 'Lists',
    'lists_empty'           => 'No lists yet.',
    'lists_add'             => '+ New List',
    'lists_delete_confirm'  => 'Delete this list?',
    'list_name'             => 'Name',
    'list_description'      => 'Description',
    'list_is_public'        => 'Public',
    'list_share_link'       => 'Share Link',
    'list_add_media'        => 'Add to List',
    'list_remove_media'     => 'Remove from List',
    'list_rss'              => 'RSS Feed',
    'list_empty'            => 'No items in this list.',

    // Tags
    'tags_title'            => 'Tags',
    'tag_name'              => 'Tag Name',
    'tag_delete_confirm'    => 'Delete this tag? It will be removed from all media.',

    // Recommenders
    'recommenders_title'    => 'Recommenders',
    'recommender_name'      => 'Name',
    'recommender_delete_confirm' => 'Delete this recommender?',

    // Import
    'import_title'          => 'Import',
    'import_upload'         => 'Upload File',
    'import_drop'           => 'Drop file here or click to browse',
    'import_supported'      => 'Supported formats: CSV, HTML bookmarks, Firefox JSON',
    'import_preview'        => 'Preview',
    'import_map_fields'     => 'Map Fields',
    'import_column'         => 'Column',
    'import_maps_to'        => 'Maps to',
    'import_ignore'         => 'Ignore',
    'import_default_type'   => 'Default media type',
    'import_process'        => 'Process',
    'import_confirm'        => 'Confirm Import',
    'import_new'            => 'New Items',
    'import_duplicates'     => 'Possible Duplicates',
    'import_invalid'        => 'Invalid Items',
    'import_keep_incoming'  => 'Keep Incoming',
    'import_keep_existing'  => 'Keep Existing',
    'import_merge'          => 'Merge',
    'import_keep_both'      => 'Keep Both',
    'import_skip'           => 'Skip',
    'import_run'            => 'Import',
    'import_complete'       => 'Import Complete',
    'import_imported'       => 'imported',
    'import_skipped'        => 'skipped',
    'import_failed'         => 'failed',
    'import_confidence_definitive' => 'Definitive match',
    'import_confidence_likely'     => 'Likely match',
    'import_confidence_possible'   => 'Possible match',

    // Duplicates
    'duplicate_title'       => 'Possible Duplicate',
    'duplicate_incoming'    => 'Incoming',
    'duplicate_existing'    => 'Existing',
    'duplicate_confidence'  => 'Confidence',
    'duplicate_reason'      => 'Reason',

    // Settings
    'settings_title'        => 'Settings',
    'settings_save'         => 'Save Settings',
    'settings_saved'        => 'Settings saved',
    'setting_site_title'    => 'Site Title',
    'setting_site_public'   => 'Public Site',
    'setting_site_public_hint' => 'Allow unauthenticated users to view your archive',
    'setting_theme'         => 'Theme',
    'setting_theme_light'   => 'Light',
    'setting_theme_dark'    => 'Dark',
    'setting_font_size'     => 'Font Size',
    'setting_contact_url'   => 'Contact URL',
    'setting_contact_url_hint' => 'Link displayed on public pages for visitors to reach you',
    'setting_items_per_page' => 'Items Per Page',
    'setting_default_sort'  => 'Default Sort',
    'setting_default_sort_direction' => 'Default Sort Direction',
    'setting_default_status_filter' => 'Default Status Filter',
    'setting_view_mode'     => 'View Mode',
    'setting_language'      => 'Language',
    'setting_lists_public_change' => 'You changed the site visibility. Would you like to update all lists to match?',
    'setting_lists_update_all' => 'Update All Lists',
    'setting_lists_update_manual' => 'I\'ll Update Manually',

    // Errors
    'error_not_found'       => 'Not found',
    'error_unauthorized'    => 'Unauthorized',
    'error_duplicate_url'   => 'That URL already exists in your archive',
    'error_duplicate_tag'   => 'A tag with that name already exists',
    'error_duplicate_recommender' => 'A recommender with that name already exists',
    'error_missing_title'   => 'Title is required',
    'error_invalid_type'    => 'Invalid media type',

    // Share / public pages
    'share_contact'         => 'Contact',
    'share_subscribe_rss'   => 'Subscribe via RSS',
    'share_powered_by'      => 'Powered by Loci',
];