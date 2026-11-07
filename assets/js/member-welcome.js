/* Progressive enhancement only: leave Simple Membership login, logout and data untouched. */
(function(){
 function init(){
  if(!document.body.classList.contains('bh-simple-membership'))return;
  var root=document.querySelector('.swpm-login-widget-logged, .swpm-logged-status, .swpm-login-widget-logged-in');
  if(!root||root.querySelector('.bh-member-welcome-heading'))return;
  var raw=root.innerText||'';
  if(!/Logged in as/i.test(raw)||!/Account Status/i.test(raw))return;
  var lines=raw.split(/\n/).map(function(s){return s.trim()}).filter(Boolean);
  function after(label){var i=lines.findIndex(function(x){return x.toLowerCase()===label.toLowerCase()});return i<0?'':(lines[i+1]||'')}
  var name=after('Logged in as'), status=after('Account Status'), level=after('Membership'), expiry=after('Account Expiry');
  if(!name||!status)return;
  var links=Array.from(root.querySelectorAll('a'));
  function linkLike(term){return links.find(function(a){return (a.textContent||'').trim().toLowerCase().includes(term)})}
  var edit=linkLike('edit profile'),logout=linkLike('logout');
  var original=document.createElement('div');original.className='bh-member-original-details';
  while(root.firstChild)original.appendChild(root.firstChild);
  var h=document.createElement('h2');h.className='bh-member-welcome-heading';h.textContent='Welcome back, '+name+'!';
  var p=document.createElement('p');p.className='bh-member-welcome-intro';p.textContent='Your Bubba Hub member area — manage your profile, family details and membership here.';
  var grid=document.createElement('div');grid.className='bh-member-status-grid';
  [['Account Status',status],['Membership',level||'—'],['Account Expiry',expiry||'—']].forEach(function(pair){
   var item=document.createElement('div');item.className='bh-member-status-item';
   var label=document.createElement('span');label.className='bh-member-status-label';label.textContent=pair[0];
   var value=document.createElement('span');value.className='bh-member-status-value';value.textContent=pair[1];
   item.append(label,value);grid.appendChild(item);
  });
  var actions=document.createElement('nav');actions.className='bh-member-actions';actions.setAttribute('aria-label','Member account actions');
  var base=(window.bhMemberWelcome&&window.bhMemberWelcome.home)||'/';
  var leader=/leader/i.test(level);
  [['Edit Profile',edit&&edit.href], [leader?'Leader Dashboard':'Manage Family Profiles',base+(leader?'leader/':'my-hub/children/')],['Membership Details',base+'membership-join/'],['Logout',logout&&logout.href]].forEach(function(pair){
   if(!pair[1])return;
   var a=document.createElement('a');a.className='bh-member-action';a.href=pair[1];a.textContent=pair[0];actions.appendChild(a);
  });
  root.append(h,p,grid,actions,original);
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
