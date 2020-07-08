$(function() {
  
  // define tour
  var tour1 = new Tour({
    debug: true,
    backdrop: true,
    placement: 'auto',
    backdropPadding: '10px',
    autoscroll:true,
    smartPlacement :true,
    basePath: location.pathname.slice(0, location.pathname.lastIndexOf('/')),
    steps: [
     {
		/* fcom.makeUrl('Cart','add') */
      path: "/catalog",
      element: "#tour-step-1",
      title: "Step 1",
      content: "Lorem Ipsum is simply dummy text of the printing and typesetting industry."
      },
     {
      path: "/catalog",
      element: "#tour-step-2",
      title: "Step 2",
      content: "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium"
      },
     {
      path: "/catalog",
      element: "#tour-step-3",
      title: "Step 3",
      content: "Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni"
      },
     {
      path: "/catalog",
      element: "#tour-step-4",
      title: "Step 4",
      content: "Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur"
      },     
     {
      path: "/catalog",
      element: "#tour-step-5",
      title: "Step 5",
      content: "At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium"
      },
	  {
      path: "/catalog",
      element: "#tour-step-6",
      title: "Step 5",
      content: "At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium"
      },
     
     /* ,     
      {
      path: "/my-products-new2.html",
      element: "#tour-step-6",
      title: "Step 6",
      content: "Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus"
     },
     {
      path: "/my-products-new2.html",
      element: "#tour-step-7",
      title: "Step 7",
      content: "Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus"
     }*/
    ]
  });
  
  // define tour
  var tour2 = new Tour({
    debug: true,
    backdrop: true,
    placement: 'auto',
    backdropPadding: '10px',
    autoscroll:true,
    smartPlacement :true,
    basePath: location.pathname.slice(0, location.pathname.lastIndexOf('/')),
    steps: [
	  {
      path: "/catalog",
      element: "#tour-step-1",
      title: "Step 1",
      content: "Lorem Ipsum is simply dummy text of the printing and typesetting industry."
      },
	  {
      path: "/catalog",
      element: "#tour-step-2",
      title: "Step 2",
      content: "Lorem Ipsum is simply dummy text of the printing and typesetting industry."
      },
      {
      path: "/catalog",
      element: "#tour-step-5",
      title: "Step 3",
      content: "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium"
      },
	  {
      path: "/catalog",
      element: "#tour-step-6",
      title: "Step 4",
      content: "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium"
      },
    ]
  });

  // init tour
  tour1.init();
  tour2.init();

  // start tour
  $('#catalog-setup-tour').click(function() {
    tour1.restart();
  });
  
  $('#custom-product-setup-tour').click(function() {
    tour2.restart();
  });
  

});
