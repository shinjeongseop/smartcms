const Common = (() => {
    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    }
    function formatCell(value) {
        if (value === null) return '<span class="badge badge--null">NULL</span>';
        return escapeHtml(String(value));
    }
    async function fetchText(url, options = {}) { const res = await fetch(url, { cache:'no-store', ...options }); return await res.text(); }
    async function fetchJson(url, options = {}) { const res = await fetch(url, { cache:'no-store', ...options }); const json = await res.json(); if(!json.success) throw new Error(json.message || '요청 실패'); return json.data; }
    async function get(url){ return await fetchJson(url); }
    async function post(url,data){ return await fetchJson(url,{ method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)}); }
    function serializeForm(form){ const formData = new FormData(form); const data = {}; for(const [key,value] of formData.entries()){ data[key]=value; } return data; }
    return { escapeHtml, formatCell, fetchText, fetchJson, get, post, serializeForm };
})();
