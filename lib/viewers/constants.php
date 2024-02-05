<script>
    const LANG_VALUES = <?=json_encode($langValues)?>;
    const SERVER_CONSTANTS = <?=json_encode($_SESSION['CONST_LIST'] ?? [])?>;
    const BX24_IS_INITED = typeof SERVER_CONSTANTS.DOMAIN != 'undefined';
</script>