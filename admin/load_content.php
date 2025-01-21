<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die('Acceso denegado');
}

function get_section_name($id) {
    $sections = [
        'hero' => 'Hero Principal',
        'about' => 'Acerca de Nosotros',
        'services' => 'Servicios',
        'portfolio' => 'Portafolio',
        'team' => 'Equipo',
        'pricing' => 'Precios',
        'faq' => 'Preguntas Frecuentes',
        'contact' => 'Contacto',
        'hero-services' => 'Hero de Servicios',
        'hogar' => 'Servicios para el Hogar',
        'comercial' => 'Servicios Comerciales',
        'industrial' => 'Servicios Industriales',
        'institucional' => 'Servicios Institucionales'
    ];
    return $sections[$id] ?? ucfirst($id);
}

function process_list_items($parent_node, $xpath, $section_id, &$index) {
    $items = [];
    $list_items = $xpath->query('.//i[contains(@class, "bi-check-circle")]/parent::*', $parent_node);
    
    foreach ($list_items as $item) {
        $content = trim($item->textContent);
        if (!empty($content)) {
            $items[] = [
                'type' => 'list_item',
                'content' => $content,
                'id' => $section_id . '_list_' . $index++
            ];
        }
    }
    
    return $items;
}

function process_section($section_node, $section_id, $xpath) {
    $section_content = [
        'id' => $section_id,
        'name' => get_section_name($section_id),
        'blocks' => []
    ];

    // Buscar todos los encabezados y párrafos dentro de esta sección
    $nodes = $xpath->query('.//h1|.//h2|.//h3|.//h4|.//h5|.//h6|.//p|.//li', $section_node);
    
    $current_heading = null;
    $first_heading_found = false;
    $first_paragraph_found = false;
    $index = 0;
    
    foreach ($nodes as $node) {
        $content = trim($node->textContent);
        if (empty($content)) continue;

        $tag_name = strtolower($node->nodeName);
        
        // Para la sección de portafolio, solo procesar el primer encabezado y párrafo
        if ($section_id === 'portfolio') {
            if (strpos($tag_name, 'h') === 0) {
                if ($first_heading_found) continue;
                $first_heading_found = true;
            } elseif ($tag_name === 'p') {
                if ($first_paragraph_found) continue;
                $first_paragraph_found = true;
            }
        }
        
        // Procesar encabezados
        if (strpos($tag_name, 'h') === 0) {
            if ($current_heading) {
                $section_content['blocks'][] = $current_heading;
            }
            $current_heading = [
                'heading' => [
                    'type' => 'heading',
                    'content' => $content,
                    'id' => $section_id . '_heading_' . $index++,
                    'level' => substr($tag_name, 1)
                ],
                'paragraphs' => [],
                'list_items' => []
            ];
        } 
        // Procesar párrafos
        elseif ($tag_name === 'p') {
            // Si es una sección especial (pricing, faq, contact), tratar el párrafo como un encabezado
            if (in_array($section_id, ['pricing', 'faq', 'contact']) && !$current_heading) {
                $current_heading = [
                    'heading' => [
                        'type' => 'heading',
                        'content' => $content,
                        'id' => $section_id . '_heading_' . $index++,
                        'level' => '3'
                    ],
                    'paragraphs' => [],
                    'list_items' => []
                ];
            } else {
                if ($current_heading) {
                    $current_heading['paragraphs'][] = [
                        'type' => 'paragraph',
                        'content' => $content,
                        'id' => $section_id . '_paragraph_' . $index++
                    ];
                } else {
                    // Si no hay un encabezado actual, crear uno temporal
                    $current_heading = [
                        'heading' => [
                            'type' => 'heading',
                            'content' => '',
                            'id' => $section_id . '_heading_' . $index++,
                            'level' => '3'
                        ],
                        'paragraphs' => [[
                            'type' => 'paragraph',
                            'content' => $content,
                            'id' => $section_id . '_paragraph_' . $index++
                        ]],
                        'list_items' => []
                    ];
                }
            }
        }
        // Procesar elementos de lista
        elseif ($tag_name === 'li') {
            // Verificar si el elemento de lista tiene el ícono bi-check
            $has_check_icon = $xpath->query('.//i[contains(@class, "bi-check")]', $node)->length > 0;
            if ($has_check_icon) {
                if (!$current_heading) {
                    $current_heading = [
                        'heading' => [
                            'type' => 'heading',
                            'content' => '',
                            'id' => $section_id . '_heading_' . $index++,
                            'level' => '3'
                        ],
                        'paragraphs' => [],
                        'list_items' => []
                    ];
                }
                $current_heading['list_items'][] = [
                    'type' => 'list_item',
                    'content' => $content,
                    'id' => $section_id . '_list_' . $index++
                ];
            }
        }
    }
    
    // Agregar el último encabezado si existe
    if ($current_heading) {
        $section_content['blocks'][] = $current_heading;
    }
    
    return $section_content;
}

function get_content_blocks($file_path) {
    if (!file_exists($file_path)) {
        return [];
    }

    $html = file_get_contents($file_path);
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $content_sections = [];

    // Para service-details.html, necesitamos manejar las pestañas de manera especial
    if (strpos($file_path, 'service-details.html') !== false) {
        // Primero agregamos la sección hero-services
        $hero_section = $xpath->query('//section[@id="hero-services"]')->item(0);
        if ($hero_section) {
            $content_sections[] = process_section($hero_section, 'hero-services', $xpath);
        }

        // Luego procesamos cada pestaña como una sección
        $tabs = $xpath->query('//div[contains(@class, "tab-pane")]');
        foreach ($tabs as $tab) {
            $tab_id = $tab->getAttribute('id');
            $content_sections[] = process_section($tab, $tab_id, $xpath);
        }

        // Finalmente agregamos la sección de contacto
        $contact_section = $xpath->query('//section[@id="contact"]')->item(0);
        if ($contact_section) {
            $content_sections[] = process_section($contact_section, 'contact', $xpath);
        }
    } else {
        // Para index.html, procesamos las secciones normalmente
        $sections = $xpath->query('//section[@id]');
        foreach ($sections as $section) {
            $section_id = $section->getAttribute('id');
            $content_sections[] = process_section($section, $section_id, $xpath);
        }
    }

    return array_filter($content_sections, function($section) {
        return !empty($section['blocks']);
    });
}

// Lista de secciones válidas
$valid_sections = [
    'hero', 'about', 'services', 'portfolio', 'team', 
    'pricing', 'faq', 'contact', 'footer'
];

$section = $_GET['section'] ?? '';

if (!in_array($section, $valid_sections)) {
    die('<div class="alert alert-danger">Sección no válida. Por favor seleccione una sección del menú.</div>');
}

$file_path = '../index.html';
$title = '';

switch ($section) {
    case 'index':
        $file_path = '../index.html';
        $title = 'Página Principal';
        break;
    case 'services':
        $file_path = '../service-details.html';
        $title = 'Servicios';
        break;
    default:
        die('Sección no válida');
}

$content_sections = get_content_blocks($file_path);
?>

<div class="container-fluid">
    <h2 class="mb-4"><?php echo htmlspecialchars($title); ?></h2>
    
    <div class="accordion" id="sectionsAccordion">
        <?php foreach ($content_sections as $section_index => $section): ?>
            <div class="accordion-item mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button <?php echo $section_index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#section<?php echo $section_index; ?>">
                        <?php echo htmlspecialchars($section['name']); ?>
                    </button>
                </h2>
                <div id="section<?php echo $section_index; ?>" class="accordion-collapse collapse <?php echo $section_index === 0 ? 'show' : ''; ?>" data-bs-parent="#sectionsAccordion">
                    <div class="accordion-body">
                        <form class="section-form" data-section-id="<?php echo htmlspecialchars($section['id']); ?>">
                            <?php foreach ($section['blocks'] as $block): ?>
                                <div class="content-section">
                                    <?php if (isset($block['heading']) && $block['heading']): ?>
                                        <div class="content-editor">
                                            <label class="form-label">Encabezado (H<?php echo $block['heading']['level']; ?>)</label>
                                            <textarea class="form-control mb-3" name="<?php echo $block['heading']['id']; ?>" rows="2"><?php echo htmlspecialchars($block['heading']['content']); ?></textarea>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php foreach ($block['paragraphs'] as $paragraph): ?>
                                        <div class="content-editor">
                                            <label class="form-label">Párrafo</label>
                                            <textarea class="form-control mb-3" name="<?php echo $paragraph['id']; ?>" rows="3"><?php echo htmlspecialchars($paragraph['content']); ?></textarea>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if (!empty($block['list_items'])): ?>
                                        <div class="list-items-section">
                                            <label class="form-label">Lista de Servicios</label>
                                            <?php foreach ($block['list_items'] as $item): ?>
                                                <div class="content-editor">
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text">
                                                            <i class="bi bi-check-circle"></i>
                                                        </span>
                                                        <textarea class="form-control" name="<?php echo $item['id']; ?>" rows="1"><?php echo htmlspecialchars($item['content']); ?></textarea>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="d-flex justify-content-center mt-4">
                                <button type="submit" class="btn btn-primary save-section px-4">
                                    <i class="bi bi-save me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
