{if $online}
<!-- Bootstrap -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
{else}
<!-- Bootstrap -->
<script src="{$path}/node_modules/jquery/dist/jquery.min.js"></script>
<script src="{$path}/node_modules/popper.js/dist/umd/popper.min.js"></script>
<script src="{$path}/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
{/if}

<script src="{$path}/resources/build/{build file='scripts.js'}"></script>