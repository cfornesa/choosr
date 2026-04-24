<?php
/**
 * Data-to-Art Studio — Artwork Endpoint
 *
 * POST   /api/artwork.php          Save a new artwork
 * PATCH  /api/artwork.php?id={id}  Update artwork metadata (title, description, tags, is_public, is_featured)
 * GET    /api/artwork.php?id={id}  Get single artwork (public if is_public=1, else owned)
 * GET    /api/artwork.php          List current user's artworks
 * DELETE /api/artwork.php?id={id}  Delete a user-owned artwork
 *
 * POST, PATCH, and DELETE require authentication.
 * GET (single) allows unauthenticated access for public artworks.
 * GET (list) requires authentication.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── Auth routing ─────────────────────────────────────────────
// POST, DELETE, and GET (list) require authentication.
// GET (single with ?id=) has optional auth — public artworks are
// accessible without a session.

$is_single_get = ($method === 'GET' && !empty($_GET['id']));

if (!$is_single_get) {
    require_once __DIR__ . '/auth/session.php';
} else {
    // Optional auth: session already started by bootstrap.php
    $currentUserId = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

// ── POST — Save artwork ──────────────────────────────────────

if ($method === 'POST') {
    // Parse JSON body
    $input = file_get_contents('php://input');
    $body = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
        exit;
    }

    // Extract and validate required fields
    $art_style_id     = $body['art_style_id'] ?? null;
    $title            = $body['title'] ?? null;
    $column_mapping   = $body['column_mapping'] ?? null;
    $palette_config   = $body['palette_config'] ?? null;
    $rendering_config = $body['rendering_config'] ?? null;
    $dataset_id       = $body['dataset_id'] ?? null;
    $is_public        = $body['is_public'] ?? ARTWORK_DEFAULT_IS_PUBLIC;

    // Extract optional metadata fields
    $description      = $body['description'] ?? null;
    $tags             = $body['tags'] ?? null;
    $is_featured      = $body['is_featured'] ?? ARTWORK_DEFAULT_IS_FEATURED;

    // Validate required fields
    $missing = [];
    if ($art_style_id === null) $missing[] = 'art_style_id';
    if ($title === null || $title === '') $missing[] = 'title';
    if ($column_mapping === null) $missing[] = 'column_mapping';
    if ($palette_config === null) $missing[] = 'palette_config';

    if (!empty($missing)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'Missing required fields: ' . implode(', ', $missing),
        ]);
        exit;
    }

    // Validate title length
    if (mb_strlen($title) > 255) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'Title must not exceed 255 characters',
        ]);
        exit;
    }

    // Validate description length (Text field but enforce reasonable limit)
    if ($description !== null && mb_strlen($description) > 10000) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'Description must not exceed 10000 characters',
        ]);
        exit;
    }

    // Validate tags length (VARCHAR(255))
    if ($tags !== null && mb_strlen($tags) > 255) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'Tags must not exceed 255 characters',
        ]);
        exit;
    }

    // Sanitize tags: remove any HTML and trim
    if ($tags !== null) {
        $tags = trim(strip_tags($tags));
    }

    // Validate is_public
    $is_public = filter_var($is_public, FILTER_VALIDATE_INT);
    if ($is_public === false) $is_public = ARTWORK_DEFAULT_IS_PUBLIC;
    $is_public = $is_public ? 1 : 0;

    // Validate is_featured
    $is_featured = filter_var($is_featured, FILTER_VALIDATE_INT);
    if ($is_featured === false) $is_featured = ARTWORK_DEFAULT_IS_FEATURED;
    $is_featured = $is_featured ? 1 : 0;

    // Validate column_mapping is an array (JSON object/array decoded)
    if (!is_array($column_mapping)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'column_mapping must be a JSON object or array',
        ]);
        exit;
    }

    // Validate palette_config is an array
    if (!is_array($palette_config)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'palette_config must be a JSON object or array',
        ]);
        exit;
    }

    // Validate rendering_config if provided (must be array or null)
    if ($rendering_config !== null && !is_array($rendering_config)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'rendering_config must be a JSON object, array, or null',
        ]);
        exit;
    }

    try {
        // Validate art_style_id exists and is active
        $style_stmt = $pdo->prepare('
            SELECT id FROM art_styles WHERE id = :id AND is_active = 1
        ');
        $style_stmt->execute([':id' => $art_style_id]);
        if (!$style_stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid art style']);
            exit;
        }

        // If dataset_id provided, verify ownership or preloaded
        if ($dataset_id !== null) {
            $dataset_id = filter_var($dataset_id, FILTER_VALIDATE_INT);
            if ($dataset_id === false || $dataset_id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error'   => 'Dataset not found or access denied',
                ]);
                exit;
            }

            $ds_stmt = $pdo->prepare('
                SELECT id, user_id, source_type
                FROM datasets
                WHERE id = :id
            ');
            $ds_stmt->execute([':id' => $dataset_id]);
            $dataset = $ds_stmt->fetch();

            if (!$dataset) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error'   => 'Dataset not found or access denied',
                ]);
                exit;
            }

            // Must be owned by user OR preloaded
            if ($dataset['user_id'] !== null && (int) $dataset['user_id'] !== $currentUserId
                && $dataset['source_type'] !== 'preloaded'
            ) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error'   => 'Dataset not found or access denied',
                ]);
                exit;
            }
        }

        // Insert artwork
        // Encode JSON fields (validate encoding doesn't fail)
        $encoded_column_mapping = json_encode($column_mapping);
        $encoded_palette_config = json_encode($palette_config);
        $encoded_rendering_config = ($rendering_config !== null) ? json_encode($rendering_config) : null;
        
        if ($encoded_column_mapping === false || $encoded_palette_config === false) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error'   => 'Failed to encode data as JSON: ' . json_last_error_msg(),
            ]);
            exit;
        }
        if ($rendering_config !== null && $encoded_rendering_config === false) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error'   => 'Failed to encode rendering_config as JSON: ' . json_last_error_msg(),
            ]);
            exit;
        }

        $insert_stmt = $pdo->prepare('
            INSERT INTO artworks
                (user_id, dataset_id, art_style_id, title, description,
                 column_mapping, palette_config, rendering_config, is_public, is_featured, tags)
            VALUES
                (:user_id, :dataset_id, :art_style_id, :title, :description,
                 :column_mapping, :palette_config, :rendering_config, :is_public, :is_featured, :tags)
        ');

        $insert_stmt->execute([
            ':user_id'          => $currentUserId,
            ':dataset_id'       => $dataset_id,
            ':art_style_id'     => $art_style_id,
            ':title'            => $title,
            ':description'      => $description,
            ':column_mapping'   => $encoded_column_mapping,
            ':palette_config'   => $encoded_palette_config,
            ':rendering_config' => $encoded_rendering_config,
            ':is_public'        => $is_public,
            ':is_featured'     => $is_featured,
            ':tags'             => $tags,
        ]);

        $artwork_id = (int) $pdo->lastInsertId();

        http_response_code(201);
        echo json_encode([
            'success'    => true,
            'artwork_id' => $artwork_id,
        ]);

    } catch (PDOException $e) {
        // Always include error details for artwork save to help debug
        $message = 'Failed to save artwork: ' . $e->getMessage() . ' [SQLSTATE: ' . $e->getCode() . ']';
        error_log($message);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $message]);
    }
    exit;
}

// ── PATCH — Update artwork metadata ──────────────────────────────

if ($method === 'PATCH') {
    $id = isset($_GET['id']) ? trim($_GET['id']) : null;

    if ($id === null || $id === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Artwork ID required']);
        exit;
    }

    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid artwork ID']);
        exit;
    }

    // Parse JSON body
    $input = file_get_contents('php://input');
    $body = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
        exit;
    }

    // Only allow updating these metadata fields
    $allowedFields = ['title', 'description', 'tags', 'is_public', 'is_featured'];
    $updates = [];
    $params = ['id' => $id, 'user_id' => $currentUserId];

    foreach ($allowedFields as $field) {
        if (isset($body[$field])) {
            switch ($field) {
                case 'title':
                    if (mb_strlen($body['title']) > 255) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Title must not exceed 255 characters']);
                        exit;
                    }
                    $updates[] = "`$field` = :$field";
                    $params[":$field"] = $body['title'];
                    break;

                case 'description':
                    if (mb_strlen($body['description']) > 10000) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Description must not exceed 10000 characters']);
                        exit;
                    }
                    $updates[] = "`$field` = :$field";
                    $params[":$field"] = $body['description'];
                    break;

                case 'tags':
                    if (mb_strlen($body['tags']) > 255) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Tags must not exceed 255 characters']);
                        exit;
                    }
                    $updates[] = "`$field` = :$field";
                    $params[":$field"] = trim(strip_tags($body['tags']));
                    break;

                case 'is_public':
                    $val = filter_var($body['is_public'], FILTER_VALIDATE_INT);
                    $updates[] = "`$field` = :$field";
                    $params[":$field"] = ($val === false ? 0 : ($val ? 1 : 0));
                    break;

                case 'is_featured':
                    $val = filter_var($body['is_featured'], FILTER_VALIDATE_INT);
                    $updates[] = "`$field` = :$field";
                    $params[":$field"] = ($val === false ? 0 : ($val ? 1 : 0));
                    break;
            }
        }
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No valid fields to update']);
        exit;
    }

    try {
        // Verify ownership
        $chk_stmt = $pdo->prepare('SELECT user_id FROM artworks WHERE id = :id AND user_id = :user_id');
        $chk_stmt->execute([':id' => $id, ':user_id' => $currentUserId]);
        if (!$chk_stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Artwork not found']);
            exit;
        }

        // Build and execute UPDATE
        $params[':id'] = $id;
        $params[':user_id'] = $currentUserId;
        $setClause = implode(', ', $updates);
        $update_sql = "UPDATE artworks SET $setClause, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute($params);

        if ($update_stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Artwork not found or not updated']);
            exit;
        }

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        $message = APP_DEBUG
            ? 'Failed to update artwork: ' . $e->getMessage()
            : 'Failed to update artwork. Please try again later.';
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $message]);
    }
    exit;
}

// ── GET — Retrieve artwork(s) ────────────────────────────────

if ($method === 'GET') {
    $id = isset($_GET['id']) ? trim($_GET['id']) : null;

    try {
        if ($id !== null && $id !== '') {
            // ── Single artwork ───────────────────────────────
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid artwork ID']);
                exit;
            }

            if ($currentUserId !== null) {
                // Authenticated: can see own private + any public
                $stmt = $pdo->prepare('
                    SELECT a.*, s.display_name AS art_style_name
                    FROM artworks a
                    JOIN art_styles s ON a.art_style_id = s.id
                    WHERE a.id = :id AND (a.is_public = 1 OR a.user_id = :user_id)
                ');
                $stmt->execute([':id' => $id, ':user_id' => $currentUserId]);
            } else {
                // Unauthenticated: only public artworks
                $stmt = $pdo->prepare('
                    SELECT a.*, s.display_name AS art_style_name
                    FROM artworks a
                    JOIN art_styles s ON a.art_style_id = s.id
                    WHERE a.id = :id AND a.is_public = 1
                ');
                $stmt->execute([':id' => $id]);
            }

            $artwork = $stmt->fetch();

            if (!$artwork) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Artwork not found']);
                exit;
            }

            // Decode JSON fields
            $artwork['column_mapping']   = json_decode($artwork['column_mapping'], true);
            $artwork['palette_config']   = json_decode($artwork['palette_config'], true);
            $artwork['rendering_config'] = json_decode($artwork['rendering_config'], true);

            echo json_encode([
                'success' => true,
                'artwork' => $artwork,
            ]);

        } else {
            // ── List user's artworks ─────────────────────────
            $stmt = $pdo->prepare('
                SELECT a.*, s.display_name AS art_style_name
                FROM artworks a
                JOIN art_styles s ON a.art_style_id = s.id
                WHERE a.user_id = :user_id
                ORDER BY a.created_at DESC
            ');
            $stmt->execute([':user_id' => $currentUserId]);
            $artworks = $stmt->fetchAll();

            // Decode JSON fields for each artwork
            foreach ($artworks as &$artwork) {
                $artwork['column_mapping']   = json_decode($artwork['column_mapping'], true);
                $artwork['palette_config']   = json_decode($artwork['palette_config'], true);
                $artwork['rendering_config'] = json_decode($artwork['rendering_config'], true);
            }
            unset($artwork);

            echo json_encode([
                'success'  => true,
                'artworks' => $artworks,
            ]);
        }

    } catch (PDOException $e) {
        $message = APP_DEBUG
            ? 'Failed to fetch artwork: ' . $e->getMessage()
            : 'Failed to fetch artwork. Please try again later.';
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $message]);
    }
    exit;
}

// ── DELETE — Delete artwork ───────────────────────────────────

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? trim($_GET['id']) : null;

    if ($id === null || $id === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Artwork ID required']);
        exit;
    }

    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid artwork ID']);
        exit;
    }

    try {
        // Verify ownership and retrieve thumbnail_path
        $stmt = $pdo->prepare('
            SELECT user_id, thumbnail_path
            FROM artworks
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->execute([':id' => $id, ':user_id' => $currentUserId]);
        $artwork = $stmt->fetch();

        if (!$artwork) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Artwork not found']);
            exit;
        }

        // Delete DB record
        $del_stmt = $pdo->prepare('DELETE FROM artworks WHERE id = :id AND user_id = :user_id');
        $del_stmt->execute([':id' => $id, ':user_id' => $currentUserId]);

        // Delete thumbnail file if it exists
        if (!empty($artwork['thumbnail_path'])) {
            $thumb_path = ARTWORK_THUMBNAIL_DIR . basename($artwork['thumbnail_path']);
            if (file_exists($thumb_path)) {
                if (!@unlink($thumb_path)) {
                    error_log("artwork.php: Failed to delete thumbnail: {$thumb_path}");
                }
            }
        }

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        $message = APP_DEBUG
            ? 'Failed to delete artwork: ' . $e->getMessage()
            : 'Failed to delete artwork. Please try again later.';
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $message]);
    }
    exit;
}

// ── Method not allowed ───────────────────────────────────────

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
