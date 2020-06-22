<h3><img src="<?= $this->url->dir() ?>plugins/Imap/imap-icon.png" alt=""/>&nbsp;Imap</h3>
<div class="panel listing">

    <h3>IMAP Configuration</h3>
	<?= $this->form->label(t('IMAP Enabled'), 'imap_enabled')  ?>
	<?= $this->form->select('imap_enabled', array(0 => 'No', 1 => 'Yes'), $values) ?>

    <?= $this->form->label(t('IMAP Server'), 'imap_server') ?>
    <?= $this->form->text('imap_server', $values) ?>

    <?= $this->form->label(t('IMAP Server Port'), 'imap_server_port') ?>
    <?= $this->form->text('imap_server_port', $values) ?>

	<?= $this->form->label(t('IMAP Server Requires Valid SSL'), 'imap_server_requires_ssl')  ?>
	<?= $this->form->select('imap_server_requires_ssl', array(0 => 'No', 1 => 'Yes'), $values) ?>

    <?= $this->form->label(t('IMAP Username'), 'imap_username') ?>
    <?= $this->form->text('imap_username', $values) ?>

    <?= $this->form->label(t('IMAP Password'), 'imap_password') ?>
    <?= $this->form->password('imap_password', $values) ?>

    <?= $this->form->label(t("IMAP Mail Prefix (Default: Add a '+' to your username)"), 'imap_mail_prefix') ?>
    <?= $this->form->text('imap_mail_prefix', $values, array(),
        isset($values['imap_username']) ?
            array('placeholder="'.htmlentities(preg_replace('/@.*$/', '', $values['imap_username'])).'+"') :
            array()) ?>

    <?= $this->form->label(t("Kanboard's XML RPC URL"), 'imap_application_url') ?>
    <?= $this->form->text('imap_application_url', $values) ?>

	<?= $this->form->label(t('User ID which creates tasks'), 'imap_guest_user_id') ?>
	<?= $this->form->text('imap_guest_user_id', $values) ?>

    <?= $this->form->label(t('Default Task Priority'), 'imap_default_priority') ?>
    <?= $this->form->text('imap_default_priority', $values, array(), array('placeholder="0"')) ?>

	<?= $this->form->label(t('Generic Note Added to Task Description'), 'imap_task_description_message') ?>
	<?= $this->form->text('imap_task_description_message', $values) ?>

    <?= $this->form->label(t('Enable Automatic Email Replies'), 'imap_automatic_replies')  ?>
	<?= $this->form->select('imap_automatic_replies', array(0 => 'No', 1 => 'Yes'), $values) ?>

    <?= $this->form->label(t('Automatic Reply Body (can reference $task_id)'), 'imap_body_message') ?>
    <?= $this->form->text('imap_body_message', $values) ?>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue">
    </div>
</div>
