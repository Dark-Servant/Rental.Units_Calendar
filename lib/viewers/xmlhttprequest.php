<?
use Frontend\AutoLoader\{File, Path};

$hashValue = (new File('xmlhttprequest.event.js'))
            ->getFilePathValueViaTemplate(Path::getBaseTemplates()[Path::AJAX])
            ->getHash();
if (empty($loader->getresult()[$hashValue])) return;

?><script>
document.addEventListener('infoserviceajax:afteropen', event => {
    if ((new URL(event.detail.args[1])).host != document.location.host) return;

    event.detail.unit.setRequestHeader('INFOSERVICE-AJAX', <?=json_encode(session_id())?>);
});
</script>