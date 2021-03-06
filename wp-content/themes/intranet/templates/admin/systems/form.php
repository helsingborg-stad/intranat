<div class="wrap" id="modularity-options">
    <h1><?php _e('Manage user systems', 'municipio-intranet'); ?></h1>

    <form method="post" action="">
        <input type="hidden" name="manage-user-systems-action" value="save">

        <?php wp_nonce_field('manage-user-systems-tags'); ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">

                <div id="post-body-content" style="display:none;">
                    <!-- #post-body-content -->
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h2 class="ui-sortable-handle"><?php _e('Save', 'municipio-intranet'); ?></h2>
                        <div class="inside">
                            <div id="major-publishing-actions" style="margin: -7px -12px -12px;width: calc(100% + 24px);">
                                <div id="publishing-action">
                                    <span class="spinner"></span>
                                    <input type="submit" value="<?php _e('Save', 'municipio-intranet'); ?>" class="button button-primary button-large" id="publish" name="publish">
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle ui-sortable-handle" style="cursor:default;"><?php _e('Available systems', 'municipio-intranet'); ?></h2>
                        <div class="inside">
                            <p>
                                <?php _e('List of available systems. To enable a system to be selectable inside a user\'s "my system" list you need to check the "selectable" checkbox.
                                To force a system into all users "my systems" list you should check the "forced" checkbox', 'municipio-intranet'); ?>.
                            </p>
                            <div class="modularity-table-metabox-wrapper">
                                <table class="modularity-table">
                                    <thead>
                                        <tr>
                                            <th><?php _e('Name', 'municipio-intranet'); ?></th>
                                            <th class="system-hidden"><?php _e('Description', 'municipio-intranet'); ?></th>
                                            <th><?php _e('Url', 'municipio-intranet'); ?></th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (\Intranet\User\Systems::getAvailabelSystems() as $system) : ?>
                                        <tr>
                                            <td><?php echo $system->name; ?></td>
                                            <td class="system-hidden"><?php echo $system->description; ?></td>
                                            <td><?php echo $system->url; ?></td>
                                            <td class="text-right">
                                                <a title="<?php _e('Edit'); ?>" href="<?php echo admin_url('admin.php?page=user-systems&edit=' . $system->id); ?>" class="btn-plain municipio-edit"><span class="dashicons dashicons-edit"></span></a>
                                                <button title="<?php _e('Remove'); ?>"  onclick="return confirm('<?php _e('Are you sure you want to remove the system?', 'municipio-intranet'); ?>');" type="submit" name="manage-user-systems-remove" value="<?php echo $system->id; ?>" class="btn-plain municipio-delete"><span class="dashicons dashicons-trash"></span></button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle ui-sortable-handle" style="cursor:default;">
                            <?php
                            if (!$isEdit) {
                                _e('Add system', 'municipio-intranet');
                            } else {
                                _e('Edit system', 'municipio-intranet');
                            }
                            ?>
                        </h2>
                        <div class="inside">
                            <p>
                                <label for="system-name"><?php _e('Name', 'municipio-intranet'); ?></label>
                                <input type="text" name="system-name" class="widefat" id="system-name" <?php if ($isEdit) : ?>value="<?php echo $isEdit->name; ?>"<?php endif; ?>>
                            </p>
                            <p>
                                <label for="system-url"><?php _e('Url', 'municipio-intranet'); ?></label>
                                <input type="text" name="system-url" class="widefat" id="system-url" <?php if ($isEdit) : ?>value="<?php echo $isEdit->url; ?>"<?php endif; ?>>
                            </p>
                            <p>
                                <label for="system-description"><?php _e('Description', 'municipio-intranet'); ?></label>
                                <textarea name="system-description" id="system-description" class="widefat"><?php echo $isEdit ? $isEdit->description : ''; ?></textarea>
                            </p>
                            <p>
                                <label><input type="checkbox" name="system-is-local" value="true" <?php checked(true, $isEdit && $isEdit->is_local ? true : false); ?>> <?php _e('System is only available from a listed local IP pattern', 'municipio-intranet'); ?></label>
                            </p>
                            <p>
                                <?php if ($isEdit) : ?>
                                <button type="submit" class="button button-primary" name="system-manager-edit-system" value="<?php echo $isEdit->id; ?>"><?php _e('Save', 'municipio-intranet'); ?></button>
                                <?php else : ?>
                                <input type="submit" class="button button-primary" name="system-manager-add-system" value="<?php _e('Add', 'municipio-intranet'); ?>">
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <?php foreach (\Intranet\User\AdministrationUnits::getAdministrationUnits() as $unit) : ?>
                    <div class="postbox">
                        <h2 class="hndle ui-sortable-handle" style="cursor:default;"><?php echo sprintf(__('Available systems for %s', 'municipio-intranet'), $unit->name); ?></h2>
                        <div class="inside">
                            <div class="modularity-table-metabox-wrapper" style="margin-top:-7px;">
                                <table class="modularity-table">
                                    <thead>
                                        <tr>
                                            <th><?php _e('Name', 'municipio-intranet'); ?></th>
                                            <th><?php _e('Url', 'municipio-intranet'); ?></th>
                                            <th style="text-align:center;"><?php _e('Selectable', 'municipio-intranet'); ?></th>
                                            <th style="text-align:center;"><?php _e('Forced', 'municipio-intranet'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (\Intranet\User\Systems::getAvailabelSystems($unit->id) as $system) : ?>
                                        <tr>
                                            <td><?php echo $system->name; ?></td>
                                            <td><?php echo $system->url; ?></td>
                                            <td style="text-align:center;"><input type="checkbox" name="selectable[<?php echo $unit->id; ?>][]" value="<?php echo $system->id; ?>" <?php checked(true, $system->selectable); ?>></td>
                                            <td style="text-align:center;"><input type="checkbox" name="forced[<?php echo $unit->id; ?>][]" value="<?php echo $system->id; ?>" <?php checked(true, $system->forced); ?>></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </form>
</div>
