<?php
// Portuguese (pt)
// Machine translated — please submit corrections via GitHub
return [
    // General
    'app_name'              => 'Loci',
    'save'                  => 'Guardar',
    'cancel'                => 'Cancelar',
    'delete'                => 'Eliminar',
    'edit'                  => 'Editar',
    'add'                   => 'Adicionar',
    'close'                 => 'Fechar',
    'confirm'               => 'Confirmar',
    'back'                  => 'Voltar',
    'next'                  => 'Seguinte',
    'done'                  => 'Concluído',
    'loading'               => 'A carregar…',
    'error'                 => 'Ocorreu um erro. Por favor tente novamente.',
    'success'               => 'Sucesso',
    'required'              => 'Obrigatório',
    'optional'              => 'Opcional',
    'search'                => 'Pesquisar',
    'filter'                => 'Filtrar',
    'sort'                  => 'Ordenar',
    'yes'                   => 'Sim',
    'no'                    => 'Não',

    // Navigation
    'nav_media'             => 'Média',
    'nav_lists'             => 'Listas',
    'nav_settings'          => 'Definições',
    'nav_import'            => 'Importar',
    'nav_logout'            => 'Terminar sessão',

    // Auth
    'login_title'           => 'Iniciar sessão',
    'login_username'        => 'Nome de utilizador',
    'login_password'        => 'Palavra-passe',
    'login_submit'          => 'Iniciar sessão',
    'login_error'           => 'Nome de utilizador ou palavra-passe inválidos',
    'login_generic_error'   => 'Ocorreu um erro. Por favor tente novamente.',
    'logout_confirm'        => 'Tem a certeza que pretende terminar sessão?',

    // Media
    'media_title'           => 'Média',
    'media_add'             => '+ Adicionar',
    'media_empty'           => 'Nenhum item encontrado.',
    'media_delete_confirm'  => 'Eliminar este item?',
    'media_mark_consumed'   => 'Marcar como consumido',
    'media_mark_queue'      => 'Colocar na fila',
    'media_add_title'       => 'Adicionar média',
    'media_edit_title'      => 'Editar média',

    // Media fields
    'field_type'            => 'Tipo',
    'field_title'           => 'Título',
    'field_author'          => 'Autor',
    'field_url'             => 'URL',
    'field_notes'           => 'Notas',
    'field_recommender'     => 'Recomendado por',
    'field_tags'            => 'Etiquetas',
    'field_tags_hint'       => 'Separadas por vírgula',
    'field_status'          => 'Estado',
    'field_isbn'            => 'ISBN',
    'field_show_name'       => 'Nome do programa',
    'field_is_dead'         => 'Link inativo',
    'field_is_paywalled'    => 'Atrás de paywall',
    'field_consumed_at'     => 'Data de consumo',
    'field_created_at'      => 'Data de adição',
    'field_book_format'     => 'Formato',

    // Media types
    'type_url'              => 'URL',
    'type_book'             => 'Livro',
    'type_movie'            => 'Filme',
    'type_podcast'          => 'Podcast',

    // Media status
    'status_queue'          => 'Fila',
    'status_consumed'       => 'Consumido',

    // Book formats
    'format_paperback'      => 'Brochura',
    'format_hardcover'      => 'Capa dura',
    'format_ebook'          => 'Livro eletrónico',

    // Filters
    'filter_all_types'      => 'Todos os tipos',
    'filter_all_status'     => 'Todos',
    'filter_all_recommenders' => 'Todos os recomendadores',
    'filter_by_tag'         => 'Filtrar por etiqueta',

    // Sort
    'sort_date_added'       => 'Data de adição',
    'sort_title'            => 'Título',
    'sort_type'             => 'Tipo',
    'sort_status'           => 'Estado',
    'sort_recommender'      => 'Recomendador',
    'sort_show_name'        => 'Nome do programa',
    'sort_newest'           => 'Mais recente primeiro',
    'sort_oldest'           => 'Mais antigo primeiro',

    // View modes
    'view_list'             => 'Lista',
    'view_card'             => 'Cartão',

    // Lists
    'lists_title'           => 'Listas',
    'lists_empty'           => 'Ainda não há listas.',
    'lists_add'             => '+ Nova lista',
    'lists_delete_confirm'  => 'Eliminar esta lista?',
    'list_name'             => 'Nome',
    'list_description'      => 'Descrição',
    'list_is_public'        => 'Público',
    'list_share_link'       => 'Link de partilha',
    'list_add_media'        => 'Adicionar à lista',
    'list_remove_media'     => 'Remover da lista',
    'list_rss'              => 'Feed RSS',
    'list_empty'            => 'Nenhum item nesta lista.',

    // Tags
    'tags_title'            => 'Etiquetas',
    'tag_name'              => 'Nome da etiqueta',
    'tag_delete_confirm'    => 'Eliminar esta etiqueta? Será removida de todos os média.',

    // Recommenders
    'recommenders_title'    => 'Recomendadores',
    'recommender_name'      => 'Nome',
    'recommender_delete_confirm' => 'Eliminar este recomendador?',

    // Import
    'import_title'          => 'Importar',
    'import_upload'         => 'Carregar ficheiro',
    'import_drop'           => 'Solte o ficheiro aqui ou clique para procurar',
    'import_supported'      => 'Formatos suportados: CSV, marcadores HTML, JSON Firefox',
    'import_preview'        => 'Pré-visualização',
    'import_map_fields'     => 'Mapear campos',
    'import_column'         => 'Coluna',
    'import_maps_to'        => 'Corresponde a',
    'import_ignore'         => 'Ignorar',
    'import_default_type'   => 'Tipo de média predefinido',
    'import_process'        => 'Processar',
    'import_confirm'        => 'Confirmar importação',
    'import_new'            => 'Novos itens',
    'import_duplicates'     => 'Possíveis duplicados',
    'import_invalid'        => 'Itens inválidos',
    'import_keep_incoming'  => 'Manter o novo',
    'import_keep_existing'  => 'Manter o existente',
    'import_merge'          => 'Fundir',
    'import_keep_both'      => 'Manter ambos',
    'import_skip'           => 'Ignorar',
    'import_run'            => 'Importar',
    'import_complete'       => 'Importação concluída',
    'import_imported'       => 'importado(s)',
    'import_skipped'        => 'ignorado(s)',
    'import_failed'         => 'falhado(s)',
    'import_confidence_definitive' => 'Correspondência definitiva',
    'import_confidence_likely'     => 'Correspondência provável',
    'import_confidence_possible'   => 'Correspondência possível',

    // Duplicates
    'duplicate_title'       => 'Possível duplicado',
    'duplicate_incoming'    => 'Entrante',
    'duplicate_existing'    => 'Existente',
    'duplicate_confidence'  => 'Confiança',
    'duplicate_reason'      => 'Razão',

    // Settings
    'settings_title'        => 'Definições',
    'settings_save'         => 'Guardar definições',
    'settings_saved'        => 'Definições guardadas',
    'setting_site_title'    => 'Título do site',
    'setting_site_public'   => 'Site público',
    'setting_site_public_hint' => 'Permitir que utilizadores não autenticados vejam o seu arquivo',
    'setting_theme'         => 'Tema',
    'setting_theme_light'   => 'Claro',
    'setting_theme_dark'    => 'Escuro',
    'setting_font_size'     => 'Tamanho da fonte',
    'setting_contact_url'   => 'URL de contacto',
    'setting_contact_url_hint' => 'Link exibido nas páginas públicas para os visitantes contactarem',
    'setting_items_per_page' => 'Itens por página',
    'setting_default_sort'  => 'Ordenação predefinida',
    'setting_default_sort_direction' => 'Direção de ordenação predefinida',
    'setting_default_status_filter' => 'Filtro de estado predefinido',
    'setting_view_mode'     => 'Modo de visualização',
    'setting_language'      => 'Idioma',
    'setting_lists_public_change' => 'Alterou a visibilidade do site. Deseja atualizar todas as listas?',
    'setting_lists_update_all' => 'Atualizar todas as listas',
    'setting_lists_update_manual' => 'Vou atualizar manualmente',

    // Errors
    'error_not_found'       => 'Não encontrado',
    'error_unauthorized'    => 'Não autorizado',
    'error_duplicate_url'   => 'Este URL já existe no seu arquivo',
    'error_duplicate_tag'   => 'Já existe uma etiqueta com este nome',
    'error_duplicate_recommender' => 'Já existe um recomendador com este nome',
    'error_missing_title'   => 'O título é obrigatório',
    'error_invalid_type'    => 'Tipo de média inválido',

    // Share / public pages
    'share_contact'         => 'Contacto',
    'share_subscribe_rss'   => 'Subscrever via RSS',
    'share_powered_by'      => 'Desenvolvido por Loci',
];