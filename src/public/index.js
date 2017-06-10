$(document).bind('keydown', function(e) {
	if(e.ctrlKey && (e.which == 83)) {
		e.preventDefault();
		return false;
	}
	if(e.metaKey && (e.which == 83)) {
		e.preventDefault();
		return false;
	}
});

window.changeFile = function(route){
	window.location = "#"+route;
	window.location.reload();
};

angular.module('livedoc', ['ui.ace', 'ui.bootstrap'])
	.run(function($rootScope){

		$rootScope.loading = true;

	})
	.controller("editorCtrl", function($http, $sce, $rootScope, $window, $uibModal){

		var that = this;

		this.pane = 'both';
		this.state = 'saved';
		this.source = '';
		this.route = $window.location.hash.substring(1);

		this.content = "";
		this.type = "html";

		if($window.localStorage.getItem('livedoc_view')){
			this.pane = $window.localStorage.getItem('livedoc_view');
		}else{
			$window.localStorage.setItem('livedoc_view', "both");
			this.pane = "both";
		}

		this.changeView = function(view){
			this.pane = view;
			$window.localStorage.setItem('livedoc_view', view)
		}

		this.waitForSave = setTimeout("");

		this.sourceChanged = function(){
			clearTimeout(this.waitForSave);
			this.state = 'unsaved...';
			this.waitForSave = setTimeout(that.save, 1000);
		};

		this.save = function(){
			if(!$rootScope.loading && that.source!='') {
				this.state = 'saving...';
				$http.post("./api/save.php", {route: that.route, source: that.source, type: that.type}, {}).then(function (res) {
					that.state = 'saved';
				}, function (res) {
					that.state = 'error !';
				});
			}else{
				this.state = 'saved';
			}
		};

		this.autoLoadHtml = function(){
			if(that.pane!='view') {
				that.loadHtml();
			}
		};

		this.loadHtml = function(){
			$http.post("./api/load_html.php", {route: that.route}, {}).then(function (res) {
				that.content = $sce.trustAsHtml(res.data.content);
			}, function () {
			});
		};


		//Called on file change only
		this.load = function(){
			$http.post("./api/load.php", {route: that.route},{}).then(function(res){
				if(res.data.error){
					if(!that.route=="" && confirm("Créer le nouveau fichier "+that.route+" ?")){
						console.log("New file created");
					}else {
						this.changeFile("index.html");
					}
				}
				that.source = res.data.source;
				that.type = res.data.type;
				$rootScope.loading = false;
			}, function(){
			});
		};

		this.changeFile = function(route){
			$window.changeFile(route);
		};

		$rootScope.changeFile = this.changeFile;

		TogetherJS.config('findRoom', "livedoc_"+that.route);
		TogetherJS();
		this.load();

		//Auto show html version
		this.loadHtml();
		setInterval(this.autoLoadHtml, 2000);


		//Finder
		this.finder = function(){
			$http.post("./api/arbo.php", {},{}).then(function(res){
				$rootScope.paths = res.data;
				$uibModal.open({
					templateUrl: 'finder.html'
				});
			}, function(){
			});
		};

	})
	.controller("viewerCtrl", function($http, $sce, $rootScope, $window){

	})
	.controller("directoryCtrl", function($http, $sce, $rootScope, $window){
		this.paths = {};
	});