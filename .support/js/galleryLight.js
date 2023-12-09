var GALLERY_IMAGE_FILES,
GALLERY_IMAGE_PATH,
GALLERY_HOST='.support/api/';
setTimeout(galleryInit,500);

/* initialize - script */
function galleryInit(){
  var gpath='.';
  /* get search location */
  var path=window.location.search.match(/p=([^\&]+)/i)
  if(path){gpath=path[1];}
  GALLERY_IMAGE_PATH=gpath;
  var title=document.getElementsByTagName('title');
  if(title&&title[0]){
    var tpath='/'+gpath.replace(/^\.\/?/,'');
    title[0].innerText=tpath;
  }return galleryLoadPath(gpath);
}
/* initialize - data */
function galleryData(data){
  if(typeof data!=='object'||data==='null'){
    if(typeof data==='string'&&data.match(/^error:/ig)){
      return alert(data);
    }return alert('Error: Failed to get data images.');
  }var el=document.getElementById('gallery-index');
  if(!el){return alert('Error: Invalid "gallery-index" ID.');}
  GALLERY_IMAGE_FILES=data;
  var head=document.createElement('div');
  var uld=document.createElement('ul');
  var ul=document.createElement('ul');
  head.classList.add('light-gallery-section');
  uld.id='lightgallery-folder';
  uld.classList.add('list-unstyled');
  uld.classList.add('row');
  ul.id='lightgallery';
  ul.classList.add('list-unstyled');
  ul.classList.add('row');
  head.appendChild(uld);
  head.appendChild(ul);
  el.appendChild(head);
  var loader=document.getElementById('gallery-loader');
  if(loader){loader.parentElement.removeChild(loader);}
  data.reverse();
  return galleryLoadImages(data,ul,uld);
}
/* load image */
function galleryLoadImages(data,ul,uld,i){
  i=i?parseInt(i):0;
  if(typeof data!=='object'||data===null||!data[i]){
    if(ul.children.length>0&&uld.children.length>0){
      uld.style.borderBottom='1px dotted #777';
    }
    if(GALLERY_IMAGE_PATH.match(/\//)){
      galleryUpDir(uld,ul.children.length);
    }return galleryStart();
  }
  if(data[i].hasOwnProperty('folder')
    &&data[i].folder.match(/^\.\/\.(support|lightgallery|thumbnail)/i)){
    i++;
    return galleryLoadImages(data,ul,uld,i);
  }
  var li=document.createElement('li');
  var an=document.createElement('a');
  var img=new Image();
  img.alt='';
  img.src=data[i].thumb;
  img.dataset.id='gallery-image-a-'+i;
  img.onload=function(e){
    var el=document.getElementById(this.dataset.id);
    if(el){
      el.removeChild(el.firstChild);
      el.appendChild(this);
    }i++;
    return galleryLoadImages(data,ul,uld,i);
  };
  an.id='gallery-image-a-'+i;
  li.dataset.src=data[i].file;
  an.appendChild(galleryImageLoader());
  li.appendChild(an);
  if(data[i].hasOwnProperty('folder')){
    an.href='?p='+data[i].folder;
    an.title=galleryBasename(data[i].folder);
    var span=document.createElement('span');
    span.innerText=galleryBasename(data[i].folder);
    an.appendChild(span);
    li.style.borderColor='#bf7';
    uld.insertBefore(li,uld.firstChild);
  }else{
    an.href=data[i].file;
    an.title=galleryBasename(data[i].file);
    ul.insertBefore(li,ul.firstChild);
  }return true;
}
/* up directory */
function galleryUpDir(uld,ulength){
  var npath=GALLERY_IMAGE_PATH.replace(/\/[^\/]+\/?$/,'');
  var li=document.createElement('li');
  var an=document.createElement('a');
  var img=new Image();
  img.alt='';
  img.src='.support/images/previous.png';
  img.style.verticalAlign='middle';
  if(npath=='.'){
    an.href=window.location.pathname;
  }else{
    an.href='?p='+npath;
  }
  an.title=galleryBasename(npath);
  li.style.width='100px';
  li.style.textAlign='center';
  li.style.lineHeight='100px';
  an.appendChild(img);
  li.appendChild(an);
  uld.insertBefore(li,uld.firstChild);
  if(ulength>0){
    uld.style.borderBottom='1px dotted #777';
  }return true;
}
/* load path */
function galleryLoadPath(path){
  var url=GALLERY_HOST
   +'?gallery-path='+path
   +'&gallery-callback=galleryData';
  return galleryLoadScript(url);
}
/* load script */
function galleryLoadScript(url){
  var s=document.createElement('script');
  s.async=true;
  s.type='text/javascript';
  s.src=url;
  document.head.appendChild(s);
  return true;
}
/* image loader */
function galleryImageLoader(){
  var iloader=new Image();
  iloader.alt='';
  iloader.src='.support/images/loading.gif';
  iloader.style.margin='0px 25px';
  return iloader;
}
/* basename */
function galleryBasename(s){
  s=typeof s==='string'?s:'';
  var p=s.match(/([^\/]+)\/?$/i)
  return p?p[1]:'';
}
/* start the gallery parsing */
function galleryStart(){
  lightGallery(document.getElementById("lightgallery"),{
    thumbnail:true,
    animateThumb:true,
    showThumbByDefault:false,
    mode:"lg-slide",
    //cssEasing:"cubic-bezier(0.680,-0.550,0.265,1.550)",
    counter:true,
    download:true,
    enableSwipe:true,
    enableDrag:true,
    speed:500
  }); 
}


