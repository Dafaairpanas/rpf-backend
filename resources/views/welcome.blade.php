<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Superadmin - API Dashboard</title>
  <style>
    :root{--bg:#0b1220;--card:#071024;--muted:#9aa6c6;--accent:#06b6d4;--success:#16a34a;--danger:#ef4444}
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter,system-ui,Arial;background:var(--bg);color:#e6eef8}
    header{padding:16px 20px;background:linear-gradient(90deg,#020617, #071a2a);display:flex;gap:12px;align-items:center}
    header h1{margin:0;font-size:16px}
    .wrap{display:grid;grid-template-columns:260px 1fr;gap:12px;padding:16px}
    .sidebar{background:var(--card);padding:12px;border-radius:10px;height:calc(100vh - 96px);overflow:auto}
    .content{background:transparent}
    .box{background:linear-gradient(180deg,#021025, #04182a);padding:12px;border-radius:10px;margin-bottom:12px;border:1px solid rgba(255,255,255,0.03)}
    .resource{padding:10px;border-radius:8px;margin-bottom:6px;cursor:pointer}
    .resource.active{background:rgba(6,182,212,0.12);border:1px solid rgba(6,182,212,0.12)}
    label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px}
    input,select,textarea{width:100%;padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,0.04);background:transparent;color:#e6eef8}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:8px}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    button{background:var(--accent);color:#012;border:0;padding:8px 10px;border-radius:8px;cursor:pointer;font-weight:600}
    button.ghost{background:transparent;border:1px solid rgba(255,255,255,0.04);color:var(--muted)}
    button.danger{background:var(--danger);color:#fff}
    table{width:100%;border-collapse:collapse;font-size:13px}
    th,td{padding:8px;border-bottom:1px solid rgba(255,255,255,0.03);text-align:left}
    pre{background:#020617;padding:10px;border-radius:8px;overflow:auto}
    .muted{color:var(--muted);font-size:13px}
    .tiny{font-size:12px;color:var(--muted)}
    .topbar{display:flex;justify-content:space-between;gap:12px}
    .header-actions{display:flex;gap:8px}
    .u-inline{display:inline-block}
    .badge{background:#122737;padding:4px 8px;border-radius:999px;font-size:12px}
    footer{padding:12px;color:var(--muted);font-size:12px}
  </style>
</head>
<body>
<header>
  <h1>Superadmin API Dashboard</h1>
  <div style="margin-left:12px;color:var(--muted);font-size:13px">Kelola semua resource — CRUD, soft delete, restore, force, upload</div>
</header>
<div class="wrap">
  <aside class="sidebar box" id="sidebar">
    <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px">
      <input id="apiBase" value="http://127.0.0.1:8000/api" style="flex:1;padding:8px;border-radius:8px;background:transparent;border:1px solid rgba(255,255,255,0.04);color:#e6eef8"/>
    </div>

    <div style="margin-bottom:10px">
      <label>Auth Token (optional)</label>
      <input id="authToken" placeholder="Bearer token" />
    </div>

    <div style="margin-bottom:8px" class="muted tiny">Resources</div>
    <div id="resourceList"></div>

    <hr style="border:0;border-top:1px solid rgba(255,255,255,0.02);margin:12px 0" />
    <div class="tiny muted">Quick Actions</div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="ghost" onclick="refresh()">Refresh list</button>
      <button class="ghost" onclick="clearOutput()">Clear output</button>
    </div>
  </aside>

  <main class="content">
    <div class="box topbar">
      <div>
        <div id="currentResourceTitle"><strong>Select resource</strong></div>
        <div class="tiny muted" id="currentResourceInfo">API ready</div>
      </div>
      <div class="header-actions">
        <div class="badge" id="totalCount">--</div>
        <button class="ghost" onclick="openCreate()">New</button>
        <button class="ghost" onclick="fetchList()">Reload</button>
      </div>
    </div>

    <div id="mainArea">
      <div id="listBox" class="box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <div style="display:flex;gap:8px;align-items:center">
            <input id="q" placeholder="search q" style="padding:6px;border-radius:6px;background:transparent;border:1px solid rgba(255,255,255,0.03)" />
            <input id="per_page" type="number" value="10" style="width:80px;padding:6px;border-radius:6px;background:transparent;border:1px solid rgba(255,255,255,0.03)" />
          </div>
          <div class="actions">
            <button onclick="fetchList()">Search</button>
            <button class="ghost" onclick="fetchList(true)">With Trashed</button>
          </div>
        </div>
        <div id="tableWrap">Select a resource to list</div>
      </div>

      <div id="formBox" class="box" style="display:none"></div>

      <div id="detailBox" class="box" style="display:none"></div>

      <div id="outputBox" class="box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <div class="tiny muted">Response / Logs</div>
          <div class="tiny muted"><span id="lastCall">-</span></div>
        </div>
        <pre id="output">Ready</pre>
      </div>
    </div>

    <footer>Tip: pilih resource di kiri → klik New / Reload / Show / Update / Delete. Untuk upload file: pilih salah satu image resource.</footer>
  </main>
</div>

<script>
// resource schema: fields to show in forms & table
const RESOURCES = {
  'roles': {title:'Roles', fields:[{k:'id',t:'number'},{k:'name',t:'text'}]},
  'users': {title:'Users', fields:[{k:'id',t:'number'},{k:'username',t:'text'},{k:'name',t:'text'},{k:'email',t:'text'},{k:'division',t:'text'},{k:'role_id',t:'number'}]},
  'master-categories': {title:'Master Categories', fields:[{k:'id'},{k:'name',t:'text'}]},
  'dimensions': {title:'Dimensions', fields:[{k:'id'},{k:'width',t:'number'},{k:'height',t:'number'},{k:'depth',t:'number'}]},
  'products': {title:'Products', fields:[{k:'id'},{k:'name',t:'text'},{k:'material',t:'text'},{k:'master_category_id',t:'number'},{k:'dimension_id',t:'number'},{k:'create_by',t:'number'},{k:'deleted_at',t:'text'}]},
  'product-images': {title:'Product Images', fields:[{k:'id'},{k:'image_url',t:'text'},{k:'product_id',t:'number'},{k:'deleted_at'}], file:true},
  'teak-images': {title:'Teak Images', fields:[{k:'id'},{k:'image_url',t:'text'},{k:'product_id',t:'number'},{k:'deleted_at'}], file:true},
  'cover-images': {title:'Cover Images', fields:[{k:'id'},{k:'image_url',t:'text'},{k:'product_id',t:'number'},{k:'deleted_at'}], file:true},
  'csrs': {title:'CSR', fields:[{k:'id'},{k:'title',t:'text'},{k:'content',t:'text'},{k:'create_by',t:'number'},{k:'deleted_at'}]},
  'news': {title:'News', fields:[{k:'id'},{k:'title',t:'text'},{k:'content',t:'text'},{k:'create_by',t:'number'},{k:'deleted_at'}]}
}

let currentResource = null
let lastResponse = null
let currentPage = 1

function $(id){return document.getElementById(id)}
function authHeader(){const t=$('authToken').value.trim(); return t?{'Authorization': t}:{}}
function base(){return $('apiBase').value.replace(/\/$/,'')+'/' }

function init(){
  const list = $('resourceList')
  for(const k in RESOURCES){
    const el = document.createElement('div')
    el.className='resource'
    el.textContent=RESOURCES[k].title + ' ('+k+')'
    el.onclick=()=>selectResource(k)
    list.appendChild(el)
  }
}

function selectResource(r){
  currentResource = r
  currentPage = 1
  // highlight
  Array.from(document.querySelectorAll('.resource')).forEach(n=>n.classList.toggle('active', n.textContent.includes('('+r+')')))
  $('currentResourceTitle').innerHTML = '<strong>'+RESOURCES[r].title+'</strong> <span class="tiny muted">('+r+')</span>'
  $('currentResourceInfo').textContent = 'Ready'
  openList()
  fetchList()
}

function clearOutput(){ $('output').textContent='Ready'; $('lastCall').textContent='-'; }
function refresh(){ if(currentResource) fetchList() }

function renderTable(items, meta){
  const cols = RESOURCES[currentResource].fields.map(f=>f.k)
  const table = document.createElement('table')
  const thead = document.createElement('thead'); const htr=document.createElement('tr')
  for(const c of cols){const th=document.createElement('th'); th.textContent=c; htr.appendChild(th)}
  thAction = document.createElement('th'); thAction.textContent='Actions'; htr.appendChild(thAction)
  thead.appendChild(htr); table.appendChild(thead)
  const tbody=document.createElement('tbody')
  items.forEach(it=>{
    const tr=document.createElement('tr')
    for(const c of cols){const td=document.createElement('td'); td.textContent = it[c]===null? '': (typeof it[c]==='object'?JSON.stringify(it[c]):it[c]||''); tr.appendChild(td)}
    const td=document.createElement('td')
    td.innerHTML = `
      <button onclick="showItem(${it.id})">Show</button>
      <button class='ghost' onclick="openEdit(${it.id})">Edit</button>
      <button class='danger' onclick="deleteItem(${it.id})">Delete</button>
      <button onclick="restoreItem(${it.id})">Restore</button>
      <button class='danger' onclick="forceItem(${it.id})">Force</button>
    `
    tr.appendChild(td)
    tbody.appendChild(tr)
  })
  table.appendChild(tbody)
  $('tableWrap').innerHTML=''
  $('tableWrap').appendChild(table)
  $('totalCount').textContent = (meta && meta.total!==undefined)?('Total: '+meta.total):items.length+' item(s)'
}

async function fetchList(withTrashed=false){
  if(!currentResource) return alert('pilih resource dulu')
  const q=$('q').value; const per=$('per_page').value||10; const page=currentPage||1
  const params = new URLSearchParams({per_page:per,page})
  if(q) params.set('q',q)
  if(withTrashed) params.set('with_trashed',1)
  const url = base()+currentResource+'?'+params.toString()
  try{
    const res = await fetch(url,{headers:{...authHeader(),'Accept':'application/json'}})
    const json = await res.json()
    lastResponse = json
    $('output').textContent = JSON.stringify(json,null,2)
    $('lastCall').textContent = url
    const items = json.data || json
    const meta = json.meta || null
    renderTable(items, meta)
  }catch(e){$('output').textContent = String(e)}
}

function openList(){ $('listBox').style.display='block'; $('formBox').style.display='none'; $('detailBox').style.display='none' }

function openCreate(){
  if(!currentResource) return alert('pilih resource')
  const cfg = RESOURCES[currentResource]
  const box = $('formBox'); box.style.display='block'; box.innerHTML=''
  const title = document.createElement('div'); title.innerHTML='<strong>Create '+cfg.title+'</strong>'
  box.appendChild(title)
  const form = document.createElement('div')
  cfg.fields.forEach(f=>{
    if(f.k==='id') return
    const lab = document.createElement('label'); lab.textContent = f.k
    const inp = f.file? document.createElement('input') : (f.t==='text'? document.createElement('input') : document.createElement('input'))
    if(f.file) inp.type='file'; else inp.type=(f.t==='number'?'number':'text')
    inp.id='f_'+f.k; form.appendChild(lab); form.appendChild(inp)
  })
  const actions = document.createElement('div'); actions.className='actions'
  const btn = document.createElement('button'); btn.textContent='Create'; btn.onclick=createItem
  const btn2 = document.createElement('button'); btn2.className='ghost'; btn2.textContent='Cancel'; btn2.onclick=openList
  actions.appendChild(btn); actions.appendChild(btn2)
  box.appendChild(form); box.appendChild(actions)
}

async function createItem(){
  const cfg=RESOURCES[currentResource];
  const hasFile=!!cfg.file
  const payload = hasFile? new FormData() : {}
  cfg.fields.forEach(f=>{ if(f.k==='id') return; const el = document.getElementById('f_'+f.k); if(!el) return; if(hasFile && el.type==='file'){ if(el.files[0]) payload.append('image', el.files[0]) } else { payload[f.k]=el.value===''?null:el.value } })
  const url = base()+currentResource
  try{
    const opts = hasFile? {method:'POST', body: payload, headers: {...authHeader()}} : {method:'POST', body: JSON.stringify(payload), headers: {...authHeader(),'Content-Type':'application/json','Accept':'application/json'}}
    const res=await fetch(url,opts); const json=await res.json(); $('output').textContent=JSON.stringify(json,null,2); fetchList()
  }catch(e){$('output').textContent=String(e)}
}

async function showItem(id){
  const url = base()+currentResource+'/'+id
  try{ const res=await fetch(url,{headers:{...authHeader(),'Accept':'application/json'}}); const json=await res.json(); $('output').textContent=JSON.stringify(json,null,2); openDetail(json) }catch(e){$('output').textContent=String(e)}
}

function openDetail(json){
  const box=$('detailBox'); box.style.display='block'; box.innerHTML=''
  const title=document.createElement('div'); title.innerHTML='<strong>Detail</strong>'
  const pre=document.createElement('pre'); pre.textContent = JSON.stringify(json,null,2)
  const close=document.createElement('button'); close.className='ghost'; close.textContent='Close'; close.onclick=()=>box.style.display='none'
  box.appendChild(title); box.appendChild(pre); box.appendChild(close)
}

function openEdit(id){
  const cfg = RESOURCES[currentResource]; const box=$('formBox'); box.style.display='block'; box.innerHTML=''
  const title=document.createElement('div'); title.innerHTML='<strong>Edit '+cfg.title+' #'+id+'</strong>'
  box.appendChild(title)
  // fetch data
  fetch(base()+currentResource+'/'+id,{headers:{...authHeader(),'Accept':'application/json'}}).then(r=>r.json()).then(data=>{
    const item = data.data || data
    const form=document.createElement('div')
    cfg.fields.forEach(f=>{ if(f.k==='id') return; const lab=document.createElement('label'); lab.textContent=f.k; const inp = f.file?document.createElement('input'):document.createElement('input'); inp.type = f.file?'file':(f.t==='number'?'number':'text'); inp.id='f_'+f.k; if(!f.file) inp.value = item[f.k] ?? ''; form.appendChild(lab); form.appendChild(inp) })
    const actions=document.createElement('div'); actions.className='actions'
    const btn=document.createElement('button'); btn.textContent='Save'; btn.onclick=()=>updateItem(id)
    const btn2=document.createElement('button'); btn2.className='ghost'; btn2.textContent='Cancel'; btn2.onclick=openList
    actions.appendChild(btn); actions.appendChild(btn2); box.appendChild(form); box.appendChild(actions)
  }).catch(e=>{$('output').textContent=String(e)})
}

async function updateItem(id){
  const cfg=RESOURCES[currentResource]; const hasFile=!!cfg.file
  const payload = hasFile? new FormData() : {}
  cfg.fields.forEach(f=>{ if(f.k==='id') return; const el=document.getElementById('f_'+f.k); if(!el) return; if(hasFile && el.type==='file'){ if(el.files[0]) payload.append('image',el.files[0]) } else { payload[f.k]=el.value===''?null:el.value } })
  const url=base()+currentResource+'/'+id
  try{
    const opts = hasFile? {method:'POST', body: payload, headers:{...authHeader()}} : {method:'PUT', body: JSON.stringify(payload), headers:{...authHeader(),'Content-Type':'application/json','Accept':'application/json'}}
    const res=await fetch(url,opts); const json=await res.json(); $('output').textContent=JSON.stringify(json,null,2); fetchList()
  }catch(e){$('output').textContent=String(e)}
}

async function deleteItem(id){ if(!confirm('Soft delete item #'+id+'?')) return; try{ const res=await fetch(base()+currentResource+'/'+id,{method:'DELETE',headers:{...authHeader()}}); const json=await res.json(); $('output').textContent=JSON.stringify(json,null,2); fetchList() }catch(e){$('output').textContent=String(e)} }
async function restoreItem(id){ try{ const res=await fetch(base()+currentResource+'/'+id+'/restore',{method:'POST',headers:{...authHeader()}}); const json=await res.json(); $('output').textContent=JSON.stringify(json,null,2); fetchList() }catch(e){$('output').textContent=String(e)} }
async function forceItem(id){ if(!confirm('Force delete item #'+id+'? This is permanent.')) return; try{ const res=await fetch(base()+currentResource+'/'+id+'/force',{method:'DELETE',headers:{...authHeader()}}); const json=await res.json(); $('output').textContent=JSON.stringify(json,null,2); fetchList() }catch(e){$('output').textContent=String(e)} }

init()
</script>
</body>
</html>
