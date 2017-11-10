<section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{{URL::asset('dist/img/avatar5.png')}}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN NAVIGATION</li>
        <li><a href="/home"><i class="fa fa-home"></i> <span>Home</span></a></li>
        <li><a href="{{ route('audio.index') }}"><i class="fa fa-home"></i> <span>Audio Streaming</span></a></li>
        <li><a href="{{  url('/video') }}"><i class="fa fa-home"></i> <span>Video Streaming</span></a></li>
        

      </ul>
    </section>
