(()=>{var e={n:a=>{var t=a&&a.__esModule?()=>a.default:()=>a;return e.d(t,{a:t}),t},d:(a,t)=>{for(var l in t)e.o(t,l)&&!e.o(a,l)&&Object.defineProperty(a,l,{enumerable:!0,get:t[l]})},o:(e,a)=>Object.prototype.hasOwnProperty.call(e,a),r:e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}},a={};(()=>{"use strict";e.r(a);const t=flarum.core.compat["admin/app"];var l=e.n(t);const c=flarum.core.compat["common/components/Link"];var r=e.n(c);const n=flarum.core.compat["common/extend"],s=flarum.core.compat["admin/components/StatusWidget"];var o=e.n(s);const i=flarum.core.compat["common/components/Button"];var p=e.n(i);function u(){l().request({url:l().forum.attribute("apiUrl")+"/lscache-purge",method:"GET"}).then((function(){l().alerts.show({type:"success"},l().translator.trans("acpl-lscache.admin.purge_all_success"))}))}l().initializers.add("acpl-lscache",(function(){l().extensionData.for("acpl-lscache").registerSetting({setting:"acpl-lscache.public_cache_ttl",label:l().translator.trans("acpl-lscache.admin.public_cache_ttl_label"),help:l().translator.trans("acpl-lscache.admin.public_cache_ttl_help"),type:"number",min:30}).registerSetting({setting:"acpl-lscache.clearing_cache_listener",label:l().translator.trans("acpl-lscache.admin.clearing_cache_listener_label"),type:"boolean"}).registerSetting({setting:"acpl-lscache.serve_stale",label:l().translator.trans("acpl-lscache.admin.serve_stale_label"),help:l().translator.trans("acpl-lscache.admin.serve_stale_help"),type:"boolean"}).registerSetting((function(){return m("div",{className:"Form-group"},m("label",{htmlFor:"purge_link_list"},l().translator.trans("acpl-lscache.admin.purge_on_discussion_update_label")),m("div",{className:"helpText"},l().translator.trans("acpl-lscache.admin.purge_on_discussion_update_help",{a:m(r(),{href:"https://docs.litespeedtech.com/lscache/devguide/controls/#cache-tag",external:!0,target:"_blank"})})),m("textarea",{id:"purge_link_list",className:"FormControl",rows:4,bidi:this.setting("acpl-lscache.purge_on_discussion_update")}))})).registerSetting((function(){return m("div",{className:"Form-group"},m("label",{htmlFor:"exclude_link_list"},l().translator.trans("acpl-lscache.admin.cache_exclude_label")),m("div",{className:"helpText"},l().translator.trans("acpl-lscache.admin.cache_exclude_help")),m("textarea",{id:"exclude_link_list",className:"FormControl",rows:4,bidi:this.setting("acpl-lscache.cache_exclude")}))})),(0,n.extend)(o().prototype,"toolsItems",(function(e){e.add("clearLSCache",m(p(),{onclick:u},l().translator.trans("acpl-lscache.admin.purge_all")))}))}))})(),module.exports=a})();
//# sourceMappingURL=admin.js.map