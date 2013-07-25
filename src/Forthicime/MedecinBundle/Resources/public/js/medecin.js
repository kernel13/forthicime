
//
//  Class: BannerSingleton
//
function BannerSingleton() {  
  var __self = this;

  // quelques propriétés  
  this.__hidden = false

  this.toggle =   toggle;
  this.show   =   show;
  this.hide   =   hide;
  this.isHidden = isHidden;
  this.setHidden = setHidden;

  if ( BannerSingleton.caller != BannerSingleton.getInstance ) {  
      throw new Error("This object cannot be instanciated");  
  }  

  // Hide the banner
  function setHidden()
  {
     $('#siteHeader').css('top', -210);
     $.cookie("banner_status", "hidden");
  }

  // Return true if the banner is currently hidden
  function isHidden()
  {
      return ($.cookie("banner_status") === "hidden");
  }

  // Hide or show the banner depending the current status
  function toggle(objThis)
  {        
     __self.__hidden = ($.cookie("banner_status") === "hidden");
     if(__self.__hidden)
        __self.show(objThis);       
     else  
        __self.hide(objThis);
  }

  // Hide the banner with an annimation
  function hide(objThis)
  {
     $('#siteHeader').animate({top: '-210'});
     $(objThis).html("<i class='icon-circle-arrow-down icon-white'>");
     $.cookie("banner_status", "hidden");
  }

  // Show the banner with an annimation
  function show(objThis)
  {
     $('#siteHeader').animate({top: '0'});
     $(objThis).html("<i class='icon-circle-arrow-up icon-white'>");
     $.cookie("banner_status", "shown");
  }

}  
  
// propriété statique qui contient l'instance unique  
BannerSingleton.instance = null;  
  
// Class method getInstance
BannerSingleton.getInstance = function() {  
  if (this.instance == null) {  
      this.instance = new BannerSingleton();  
  }  
  
  return this.instance;  
}  