;(function(open, send) {

    /**
     * Замена метода open у класса XMLHttpRequest методом, который перед вызовом
     * оригинального метода вызовет все обработчики события infoserviceajax:open
     * с передачей обработчикам экземпляра класса XMLHttpRequest и параметров,
     * переданных методу
     *
     * @return void
     */
    XMLHttpRequest.prototype.open = function() {
        var event = new CustomEvent('infoserviceajax:open', {detail: {unit: this, args: arguments}});
        document.dispatchEvent(event);
        let result = open.call(this, ...arguments);

        var event = new CustomEvent('infoserviceajax:afteropen', {detail: {unit: this, args: arguments}});
        document.dispatchEvent(event);
        return result;
    }

    /**
     * Замена метода send у класса XMLHttpRequest методом, который перед вызовом
     * оригинального метода вызовет все обработчики события infoserviceajax:send
     * с передачей обработчикам экземпляра класса XMLHttpRequest и параметров,
     * переданных методу
     *
     * @return void
     */
    XMLHttpRequest.prototype.send = function() {
        var event = new CustomEvent('infoserviceajax:send', {detail: {unit: this, args: arguments}});
        document.dispatchEvent(event);
        let result = send.call(this, ...arguments);

        var event = new CustomEvent('infoserviceajax:aftersend', {detail: {unit: this, args: arguments}});
        document.dispatchEvent(event);
        return result;
    }
})(XMLHttpRequest.prototype.open, XMLHttpRequest.prototype.send);