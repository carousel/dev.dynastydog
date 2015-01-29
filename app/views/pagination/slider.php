<?php
    $presenter = new Illuminate\Pagination\BootstrapPresenter($paginator);
    $total = $paginator->getTotal();

    if ($paginator->getLastPage() > 1)
    {
?>

<section class="text-center">
    <ul class="pagination">
        <?php echo $presenter->render(); ?>
    </ul>
</section>

<?php
    }
?>