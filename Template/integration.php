<h3><img src="<?= $this->url->dir() ?>plugins/Imap/imap-icon.png"/>&nbsp;Imap</h3>
<div class="listing">

    <h3>IMAP Configuration</h3>
    <?= $this->form->label(t('IMAP Server'), 'imap_server') ?>
    <?= $this->form->text('imap_server', $values) ?>

    <?= $this->form->label(t('IMAP Server Port'), 'imap_server_port') ?>
    <?= $this->form->text('imap_server_port', $values) ?>

    <?= $this->form->label(t('IMAP Server Requires SSL (0/1)'), 'imap_server_requires_ssl') ?>
    <?= $this->form->text('imap_server_requires_ssl', $values) ?>

    <?= $this->form->label(t('IMAP Username'), 'imap_username') ?>
    <?= $this->form->text('imap_username', $values) ?>

    <?= $this->form->label(t('IMAP Password'), 'imap_password') ?>
    <?= $this->form->password('imap_password', $values) ?>

    <?= $this->form->label(t("Mail prefix (Default: Add a '+' to your username)"), 'imap_mail_prefix') ?>
    <?= $this->form->text('imap_mail_prefix', $values) ?>

    <?= $this->form->label(t("Kanboard's XML RPC URL"), 'imap_application_url') ?>
    <?= $this->form->text('imap_application_url', $values) ?>

    <?= $this->form->label(t('User ID which creates tasks'), 'imap_guest_user_id') ?>
    <?= $this->form->text('imap_guest_user_id', $values) ?>

    <?= $this->form->label(t('Default task priority'), 'imap_default_priority') ?>
    <?= $this->form->text('imap_default_priority', $values) ?>

    <?= $this->form->label(t('Email automatic reply (can reference $task_id)'), 'imap_body_message') ?>
    <?= $this->form->text('imap_body_message', $values) ?>

    <?= $this->form->label(t('Generic note added to task description'), 'imap_task_description_message') ?>
    <?= $this->form->text('imap_task_description_message', $values) ?>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue">
    </div>
</div>
