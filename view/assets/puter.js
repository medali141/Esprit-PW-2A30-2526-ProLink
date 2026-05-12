(function(global){
    // Minimal Puter.js micro-library for simple state+render bindings
    function Puter(initialState, renderer){
        this.state = initialState || {};
        this.renderer = (typeof renderer === 'function') ? renderer : function(){};
        this.subs = [];
        try { this.renderer(this.state); } catch(e){}
    }

    Puter.prototype.setState = function(partial){
        if (!partial || typeof partial !== 'object') return;
        var changed = false;
        for (var k in partial){
            if (Object.prototype.hasOwnProperty.call(partial,k)){
                this.state[k] = partial[k];
                changed = true;
            }
        }
        if (changed){
            try { this.renderer(this.state); } catch(e){}
            for (var i=0;i<this.subs.length;i++){
                try { this.subs[i](this.state); } catch(e){}
            }
        }
    };

    Puter.prototype.subscribe = function(fn){
        if (typeof fn !== 'function') return function(){};
        this.subs.push(fn);
        try { fn(this.state); } catch(e){}
        var self = this;
        return function(){
            var idx = self.subs.indexOf(fn);
            if (idx >= 0) self.subs.splice(idx,1);
        };
    };

    function create(opts){
        opts = opts || {};
        var s = opts.state || {};
        var r = opts.render || opts.renderer || function(){};
        return new Puter(s, r);
    }

    global.Puter = { create: create };
})(window);
