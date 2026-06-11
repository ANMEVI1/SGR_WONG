<tr>
    <td>
        <div style="display: flex; align-items: center; gap: 12px;">
            <?php 
            // Placeholder SVG inline que nunca falla
            $placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Crect fill='%23f0ece4' width='60' height='60'/%3E%3Ctext fill='%23c9954a' font-size='10' font-weight='bold' x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle'%3E?%3C/text%3E%3C/svg%3E";
            
            $img = $p['Imagen_URL'] ?: $placeholderSvg;
            if (strpos($img, '../') !== 0 && strpos($img, 'assets/') === 0) {
                $img = '../../../' . $img;
            }
            ?>
            <img class="plato-img" 
                 src="<?= htmlspecialchars($img) ?>" 
                 alt="<?= htmlspecialchars($p['Nombre']) ?>" 
                 loading="lazy" 
                 onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2760%27 height=%2760%27%3E%3Crect fill=%27%23f0ece4%27 width=%2760%27 height=%2760%27/%3E%3Ctext fill=%27%23c9954a%27 font-size=%2710%27 font-weight=%27bold%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dominant-baseline=%27middle%27%3E?%3C/text%3E%3C/svg%3E';">
            <div>
                <strong><?= htmlspecialchars($p['Nombre']) ?></strong>
            </div>
        </div>
    </td>
    <td style="text-align: center;">
        <label class="toggle-switch">
            <input type="checkbox" 
                   onchange="toggleFlag('top', <?= (int)$p['PlatoID'] ?>, this.checked)" 
                   <?= !empty($p['Es_Top']) ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
        </label>
    </td>
    <td style="text-align: center;">
        <label class="toggle-switch">
            <input type="checkbox" 
                   onchange="toggleFlag('promo', <?= (int)$p['PlatoID'] ?>, this.checked)" 
                   <?= !empty($p['Es_Promo']) ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
        </label>
    </td>
    <td style="text-align: center;">
        <a href="../menu/form.php?id=<?= (int)$p['PlatoID'] ?>" 
           class="btn btn-sm" 
           style="background: #17a2b8; color: white; padding: 8px 12px; text-decoration: none; display: inline-block; border-radius: 4px;">
            <i class="fas fa-edit"></i> Editar
        </a>
    </td>
</tr>
