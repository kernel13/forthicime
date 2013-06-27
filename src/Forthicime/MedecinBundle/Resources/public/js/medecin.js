
//
//  Class: BannerSingleton
//
function BannerSingleton() {  
  
  // quelques propriétés  
  this.__hidden = false

  this.hidden = hidden;

  if ( BannerSingleton.caller != BannerSingleton.getInstance ) {  
      throw new Error("This object cannot be instanciated");  
  }  

  function hidden(value)
  {
      value = typeof value !== 'undefined' ? value : null;
      if(value = null){
        //this.__hidden = $.cookie("hidden");
        return this.__hidden;        
      }
      else{
        this.__hidden = value;
        //$.cookie("hidden", value);
      }
  }
}  
  
// propriété statique qui contient l'instance unique  
BannerSingleton.instance = null;  
  
BannerSingleton.getInstance = function() {  
  if (this.instance == null) {  
      this.instance = new BannerSingleton();  
  }  
  
  return this.instance;  
}  