<?php echo $this->extend('Back/layout/main'); ?>

<?php echo $this->section('title') ?>
<?php echo $title ?? 'home'; ?>
<?php echo $this->endSection('title') ?>

<?php echo $this->section('css') ?>


<?php echo $this->endSection('css') ?>
<?php echo $this->section('content') ?>
<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"><?php echo $title ?? 'home'; ?></h1>

</div>
<!-- /.container-fluid -->

<?php echo $this->endSection('content') ?>


<?php echo $this->section('js') ?>


<?php echo $this->endSection('js') ?>