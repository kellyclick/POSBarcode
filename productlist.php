<?php
include_once 'connectdb.php';
session_start();

if($_SESSION['useremail']==""){

    header('location:../index.php');
}
include_once "header.php";


?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
        
          </div><!-- /.col -->
          <div class="col-sm-6">
           
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-12">
          

           
          </div>
          <!-- /.col-md-6 -->
      
          <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Product List :</h5>
              </div>
              <div class="card-body">
              <table class="table table-striped table-hover" id="table_product">


<thead>


    <tr>
        <td>Barcode</td>
        <td>Product</td>
        <td>Category</td>
        <td>Description</td>
        <td>Stock</td>
        <td>Purchaseprice</td>
        <td>Saleprice</td>
        <td>Image</td>
        <td>ActionIcons</td>
    </tr>

</thead>

<?php

$select = $pdo->prepare("select * from tbl_product order by pid ASC");
$select->execute();

while ($row = $select->fetch(PDO::FETCH_OBJ)) {

    echo '
    <tr>
    <td>' . $row->barcode . '</td>
    <td>' . $row->product . '</td>
    <td>' . $row->category . '</td>
    <td>' . $row->description. '</td>
    <td>' . $row->stock . '</td>
    <td>' . $row->purchaseprice . '</td>
    <td>' . $row->saleprice. '</td>
    <td><image src="productimage/'.$row->image.'" class="img-rounded" width="40px" height="40px"></td>

 
<td>    
<div class="btn-group">
<a href="printbarcode.php?id='.$row->pid.'" class="btn btn-dark btn-xs" role="button"><span class="fa fa-barcode" style="color:#ffffff" data-toggle="tooltip" title="PrintBarcode"></span></a>


<a href="viewproduct.php?id='.$row->pid.'" class="btn btn-warning btn-xs" role="button"><span class="fa fa-eye" style="color:#ffffff" data-toggle="tooltip" title="View Product"></span></a>

<a href="editproduct.php?id='.$row->pid.'" class="btn btn-success btn-xs" role="button"><span class="fa fa-edit" style="color:#ffffff" data-toggle="tooltip" title="Edit Product"></span></a>


<button id='.$row->pid.'  class="btn btn-danger btn-xs btndelete"><span class="fa fa-trash" style="color:#ffffff" data-toggle="tooltip" title="Delete Product"></span></button>


</div>


</td>

    </tr>';

}



?>


<tbody>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        $(document).ready(function() {
            $('.delete-btn').click(function(e) {
                e.preventDefault();

                var userId = $(this).data('id');

                Swal.fire({
                    title: 'Confirmation',
                    text: 'Are you sure you want to delete this? Once deleted, you will not be able to recover this account!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d63032',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'registration.php?id=' + userId;
                    }
                });
            });
        });
    </script>

</tbody>


</table>


              </div>
            </div>

           
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  


  <?php

include_once"footer.php";


?>

<script>
$(document).ready( function () {
    $('#table_product').DataTable();
});
</script>

<script>
$(document).ready( function () {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>


<script>
$(document).ready(function () {
  $('.btndelete').click(function(e) {

    Swal.fire({
  title: "Are You Sure You Want To Delete This Product!!!",
  text: "You Won't Be Able To Recover This Product!!!",
  icon: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3085d6",
  cancelButtonColor: "#d33",
  confirmButtonText: "Yes, delete it!"
}).then((result) => {
  if (result.isConfirmed) {


    $.ajax({
                           url: 'productdelete.php',
                           type: 'post',
                           data: {
                           pidd: id
                     },
                     success: function (data) {
    tdt.parents('tr').hide();
}

        });



    Swal.fire({
      title: "Your Product Is Deleted!!!",
      text: "The Product Has Been Deleted Successfully!!!",
      icon: "success"
    });
  }
});





        var tdt = $(this);
        var id = $(this).attr('id');

         
    });
});

</script>
