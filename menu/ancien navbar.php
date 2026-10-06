<!--
 Start Preloader
 ==================================== -->
<!--
End Preloader
==================================== -->
<!--
Fixed Navigation
==================================== -->
<style>
    header{
        background-color: #ffffff;
        box-shadow: 0px 3px 2px;
    }

    li.active{
        color: cornflowerblue!important;
    }
</style>
<header class="navigation fixed-top">
    <div class="container">
        <!-- main nav -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <!-- logo -->
            <a class="navbar-brand logo" href="?pages=home">
                <img class="logo-default" src="./media/lo/logo1.jpg" style="width: 50px;border-radius: 50%!important; " alt="logo"/>
                <img class="logo-white" src="./media/lo/logo1.jpg" style="width: 50px;border-radius: 50%;" alt="logo"/>
            </a>
            <span ><b><a href="?pages=home" style="font-size: 30px; color: #002752;position: relative;left: -13px;top: 6px"> R.C.R</a></b> </span>
            <!-- /logo -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation"
                    aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navigation">
                <ul class="navbar-nav ml-auto text-center">
                    <li class="nav-item <?php echo ($page=='home') ? 'active':''; ?>">
                        <a class="nav-link" href="?pages=home">Accuiel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./adhere/adhesion.php">ADHÉSION</a>
                    </li>
                    <li class="nav-item <?php echo ($page=='equipe') ? 'active':''; ?>">
                        <a class="nav-link" href="?pages=equipe">Equipes</a>
                    </li>

                    <li class="nav-item <?php echo ($page=='contact') ? 'active':''; ?>">
                        <a class="nav-link" href="?pages=contact">Contact</a>
                    </li>

                </ul>
            </div>
        </nav>
        <!-- /main nav -->
    </div>
</header>
<!--
End Fixed Navigation
==================================== -->












<header class="navigation fixed-top">
    <div class="container">
        <!-- main nav -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <!-- logo -->
            <a class="navbar-brand logo" href="../index.php">
                <img class="logo-default" src="../media/lo/logo1.jpg" style="width: 50px;border-radius: 50%!important; " alt="logo"/>
                <img class="logo-white" src="../media/lo/logo1.jpg" style="width: 50px;border-radius: 50%;" alt="logo"/>
            </a>
            <span ><b><a href="../index.php?pages=home" style="font-size: 30px; color: #002752;position: relative;left: -13px;top: 6px"> R.C.R</a></b> </span>
            <!-- /logo -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation"
                    aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navigation">
                <ul class="navbar-nav ml-auto text-center">
                    <li class="nav-item <?php echo ($page=='home') ? 'active':''; ?>">
                        <a class="nav-link" href="../index.php?pages=home">Accuiel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" style="color: #28ABE3" href="adhesion.php">ADHÉSION</a>
                    </li>
                    <li class="nav-item <?php echo ($page=='equipe') ? 'active':''; ?>">
                        <a class="nav-link" href="../index.php?pages=equipe">Equipes</a>
                    </li>

                    <li class="nav-item <?php echo ($page=='contact') ? 'active':''; ?>">
                        <a class="nav-link" href="../index.php?pages=contact">Contact</a>
                    </li>

                </ul>
            </div>
        </nav>
        <!-- /main nav -->
    </div>
</header>