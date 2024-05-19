<?php
ob_start();

include_once 'connectdb.php';
session_start();
include_once "header.php";

function fill_product($pdo) {
  $output = '';
  $select = $pdo->prepare("SELECT * FROM tbl_product ORDER BY product ASC");
  $select->execute();
  $result = $select->fetchAll();
  foreach ($result as $row) {
    $output .= '<option value="' . $row['pid'] . '">' . $row['product'] . '</option>';
  }
  return $output;
}

$id = $_GET['id'];

$select = $pdo->prepare("SELECT * FROM tbl_invoice WHERE invoice_id = :id");
$select->bindParam(':id', $id);
$select->execute();
$row = $select->fetch(PDO::FETCH_ASSOC);

$order_date = date('Y-m-d', strtotime($row['order_date']));
$subtotal = $row['subtotal'];
$sgst = $row['sgst'];
$cgst = $row['cgst'];
$discount = $row['discount'];
$total = $row['total'];
$paid = $row['paid'];
$due = $row['due'];
$payment_type = $row['payment_type'];

$select = $pdo->prepare("SELECT * FROM tbl_invoice_details WHERE invoice_id = :id");
$select->bindParam(':id', $id);
$select->execute();
$row_invoice_details = $select->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['btnupdateorder'])) {
  $orderdate = date('Y-m-d');
  $subtotal = $_POST['txtsubtotal'];
  $discount = $_POST['txtdiscount'];
  $sgst = $_POST['txtsgst'];
  $cgst = $_POST['txtcgst'];
  $total = $_POST['txttotal'];
  $payment_type = $_POST['rb'];
  $due = $_POST['txtdue'];
  $paid = $_POST['txtpaid'];

  $arr_pid = $_POST['pid_arr'];
  $arr_barcode = $_POST['barcode_arr'];
  $arr_name = $_POST['product_arr'];
  $arr_stock = $_POST['stock_c_arr'];
  $arr_qty = $_POST['quantity_arr'];
  $arr_price = $_POST['price_c_arr'];
  $arr_total = $_POST['saleprice_arr'];

  foreach ($row_invoice_details as $product_invoice_details) {
    $update_product_stock = $pdo->prepare("UPDATE tbl_product SET stock = stock + :qty WHERE pid = :pid");
    $update_product_stock->bindParam(':qty', $product_invoice_details['qty']);
    $update_product_stock->bindParam(':pid', $product_invoice_details['product_id']);
    $update_product_stock->execute();
  }

  $delete_invoice_details = $pdo->prepare("DELETE FROM tbl_invoice_details WHERE invoice_id = :id");
  $delete_invoice_details->bindParam(':id', $id);
  $delete_invoice_details->execute();

  $update = $pdo->prepare("UPDATE tbl_invoice SET order_date = :order_date, subtotal = :subtotal, discount = :discount, sgst = :sgst, cgst = :cgst, total = :total, payment_type = :payment_type, due = :due, paid = :paid WHERE invoice_id = :id");
  $update->bindParam(':order_date', $orderdate);
  $update->bindParam(':subtotal', $subtotal);
  $update->bindParam(':discount', $discount);
  $update->bindParam(':sgst', $sgst);
  $update->bindParam(':cgst', $cgst);
  $update->bindParam(':total', $total);
  $update->bindParam(':payment_type', $payment_type);
  $update->bindParam(':due', $due);
  $update->bindParam(':paid', $paid);
  $update->bindParam(':id', $id);
  $update->execute();

  for ($i = 0; $i < count($arr_pid); $i++) {
    $selectpdt = $pdo->prepare("SELECT * FROM tbl_product WHERE pid = :pid");
    $selectpdt->bindParam(':pid', $arr_pid[$i]);
    $selectpdt->execute();
    while ($rowpdt = $selectpdt->fetch(PDO::FETCH_OBJ)) {
      $db_stock[$i] = $rowpdt->stock;
      $rem_qty = $db_stock[$i] - $arr_qty[$i];
      if ($rem_qty < 0) {
        echo "Order is not completed";
        exit;
      } else {
        $update = $pdo->prepare("UPDATE tbl_product SET stock = :rem_qty WHERE pid = :pid");
        $update->bindParam(':rem_qty', $rem_qty);
        $update->bindParam(':pid', $arr_pid[$i]);
        $update->execute();
      }
    }

    $insert_detail = $pdo->prepare("INSERT INTO tbl_invoice_details (invoice_id, barcode, product_id, product_name, qty, rate, saleprice, order_date) VALUES (:invid, :barcode, :pid, :name, :qty, :rate, :saleprice, :order_date)");
    $insert_detail->bindParam(':invid', $id);
    $insert_detail->bindParam(':barcode', $arr_barcode[$i]);
    $insert_detail->bindParam(':pid', $arr_pid[$i]);
    $insert_detail->bindParam(':name', $arr_name[$i]);
    $insert_detail->bindParam(':qty', $arr_qty[$i]);
    $insert_detail->bindParam(':rate', $arr_price[$i]);
    $insert_detail->bindParam(':saleprice', $arr_total[$i]);
    $insert_detail->bindParam(':order_date', $orderdate);

    if (!$insert_detail->execute()) {
      print_r($insert_detail->errorInfo());
    }
  }

  header('Location: orderlist.php');
}

$select = $pdo->prepare("SELECT * FROM tbl_taxdis");
$select->execute();
$row = $select->fetch(PDO::FETCH_OBJ);

ob_end_flush();
?>









<style type="text/css">
  .tableFixHead {
    overflow: scroll;
    height: 520px;
  }

  .tableFixHead thead th {
    position: sticky;
    top: 0;
    z-index: 1;
  }

  table {
    border-collapse: collapse;
    width: 100px;
  }

  th,
  td {
    padding: 8px 16px;
  }

  th {
    background: #eee;
  }
</style>




<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <!-- <h1 class="m-0">point of sale</h1> -->
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <!-- <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Starter Page</li> -->
          </ol>
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

     

            <div class="card card-danger card-outline">
              <div class="card-header">
                <!-- <a href="dashboard.php"  style='text-align:left;vertical-align:middle; font-size:17px;'><span class='badge badge-info' class="btn btn-info"><span class="report-count">Back Dashboard</span></a> -->

                <h5 class="m-0"> Edit ORDER POS</h5>          
              </div>
              <div class="card-body">

                <div class="row">
                  <div class="col-md-8">


                    <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-barcode"></i></span>
                      </div>
                      <input type="text" class="form-control" placeholder="Scan Barcode" name="txtbarcode" id="txtbarcode_id">
                    </div>

               

                    <form action="" method="post" name="">

                    <select class="form-control select2" data-dropdown-css-class="select2-purple" style="width: 100%;">
                      <option>Select OR search<?php echo fill_product($pdo); ?></option>

                    </select>

                    </br>
                    <div class="tableFixHead">

                      <table id="producttable" class="table table-bordered table-hover">
                        <thead>
                          <tr>
                            <th>Product </th>
                            <th>Stock </th>
                            <th>Price </th>
                            <th>QTY </th>
                            <th>Total </th>
                            <th>Del </th>
                          </tr>
                        </thead>

                        <tbody class="details" id="itemtable">
                          <tr data-widget="expandable-table" aria-expanded="false">



                          </tr>

                        </tbody>
                      </table>


                    </div>



                  </div>

                  <div class="col-md-4">

                  <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">SUBTOTAL(₱)</span>
                  </div>
                  <input type="text" class="form-control" name="txtsubtotal"  value=<?php echo $subtotal; ?> id="txtsubtotal_id" readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">₱</span>
                  </div>
                </div>

                <div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text">DISCOUNT(%)</span>
    </div>
    <input type="text" class="form-control" name="txtdiscount"id="txtdiscount_p"value="<?php echo $row->discount; ?>">

    <div class="input-group-append">
        <span class="input-group-text">%</span>
    </div>
</div>

                   

                <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">DISCOUNT(₱)</span>
                  </div>
                  <input type="text" class="form-control" id="txtdiscount_n"  readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">₱</span>
                  </div>
                </div> 


                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">SGST(%)</span>
                      </div>
                      <input type="text" class="form-control" name="txtsgst" id="txtsgst_id_p" value="<?php echo $row->sgst; ?>" readonly>
                      <div class="input-group-append">
                        <span class="input-group-text">%</span>
                      </div>
                    </div>


                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">CGST(%)</span>
                      </div>
                      <input type="text" class="form-control" name="txtcgst" id="txtcgst_id_p" value="<?php echo $row->cgst; ?>" readonly>
                      <div class="input-group-append">
                        <span class="input-group-text">%</span>
                      </div>
                    </div>

                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">SGST(₱)</span>
                      </div>
                      <input type="text" class="form-control" id="txtsgst_id_n" readonly>
                      <div class="input-group-append">
                        <span class="input-group-text">₱</span>
                      </div>
                    </div>


                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">CGST(₱)</span>
                      </div>
                      <input type="text" class="form-control" id="txtcgst_id_n" readonly>
                      <div class="input-group-append">
                        <span class="input-group-text">₱</span>
                      </div>
                    </div>

                    <hr style="height: 2px; border-width:0; color:black; background-color:black;">



                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">TOTAL(₱)</span>
                      </div>
                      <input type="text" class="form-control form-control-lg total" value=<?php echo $total; ?> name="txttotal" id="txttotal" readonly>
                      <div class="input-group-append">
                        <span class="input-group-text">₱</span>
                      </div>
                    </div>

                    <hr style="height: 2px; border-width:0; color:black; background-color:black;">



                    <div class="icheck-success d-inline">
                      <input type="radio" name="rb" value="Cash" checked id="radioSuccess1">
                      <label for="radioSuccess1">
                        CASH
                      </label>
                    </div>
                    <div class="icheck-primary d-inline">
                      <input type="radio" name="rb" value="Card" id="radioSuccess2">
                      <label for="radioSuccess2">
                        CARD
                      </label>
                    </div>
                    <div class="icheck-danger d-inline">
                      <input type="radio" name="rb" value="Check" id="radioSuccess3">
                      <label for="radioSuccess3">
                        CHECK
                      </label>

                    </div>
                    <hr style="height: 2px; border-width:0; color:blue; background-color:blue;">



                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">DUE(₱)</span>
                      </div>
                      <input type="text" class="form-control"  value=<?php echo $due; ?> name="txtdue" id="txtdue" readonly>
                      <div class="input-group-append">
                        <span class="input-group-text">₱</span>
                      </div>
                    </div>



                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">PAID(₱) </span>
                      </div>
                      <input type="text" class="form-control" value=<?php echo $paid; ?> name="txtpaid" id="txtpaid">
                      <div class="input-group-append">
                        <span class="input-group-text">₱</span>
                      </div>
                    </div>

                  


                    <hr style="height: 2px; border-width:0; color:black; background-color:black;">


                    <div class="card-footer">


                      <div class="text-center">
                        <div class="text-center">
                          <button type="submit" class="btn btn-info" name="btnupdateorder">Update Order</button>
                        </div>
                      </div>


                    </div>


                  </div>


                </div>





              </div>


            </div>


          </form>

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

include_once("footer.php");

?>

<script>
  //Initialize Select2 Elements
  $('.select2').select2()

  //Initialize Select2 Elements
  $('.select2bs4').select2({
    theme: 'bootstrap4'
  })

  var productarr = [];


  $.ajax({
    url: "getorderproduct.php",
    method: "get",
    datatype: "json",
    data: {
      id: <?php echo $_GET['id'] ?>
    },
    success: function(data) {
    //   console.log(data);
    //   alert('pid');


      $.each(data, function(key, data) {
        if (jQuery.inArray(data["product_id"], productarr) !== -1) {

          var actualqty = parseInt($('#qty_id' + data["product_id"]).val()) + 1;
          $('#qty_id' + data["product_id"]).val(actualqty);

          var saleprice = parseInt(actualqty) * data["saleprice"];

          $('#saleprice_id' + data["product_id"]).html(saleprice);
          $('#saleprice_idd' + data["product_id"]).val(saleprice);

          // $("#txtbarcode_id").val("");

          calculate(0, 0);
          $('#txtpaid').val("");
          $('#txtdue').val("");




        } else {

          addrow(data["product_id"], data["product_name"], data["qty"], data["rate"], data["saleprice"], data["stock"], data["barcode"]);

          productarr.push(data["product_id"]);

          // $("#txtbarcode_id").val("");

          function addrow(product_id, product_name, qty, rate, saleprice, stock, barcode) {

            var tr = '<tr>' +

              '<input type="hidden" class="form-control barcode" name="barcode_arr[]" id="barcode_id' + barcode + '" value="' + barcode + '"></td>' +

              '<td style="text-align:left; vertical-align:middle; font-size:17px;"><class="form-control product_c" name="product_arr[]"  <span class="badge badge-dark">' + product_name + '</span><input type="hidden" class="form-control pid" name="pid_arr[]" value="' + product_id + '"><input type="hidden" class="form-control product" name="product_arr[]" value="' + product_name + '"> </td>' +

              '<td style="text-align:left;vertical-align:middle; font-size:17px;"><span class="badge badge-primary stocklbl" name="stock_arr[]" id="stock_id' + product_id + '">' + stock + '<span><input type="hidden" class="form-control stock_C" name="stock_c_arr[]" id="stock_idd' + product_id + '" value="' + stock + '"></td>' +

              '<td style="text-align:left;vertical-align:middle; font-size:17px;"><span class="badge badge-warning price" name="price_arr[]" id="price_id' + product_id + '">' + saleprice + '<span><input type="hidden" class="form-control price_C" name="price_c_arr[]" id="price_idd' + product_id + '" value="' + saleprice + '"></td>' +

              '<td><input type="text" class="form-control qty" name="quantity_arr[]" id="qty_id' + product_id + '" value="' + qty + '" size="1"></td>' +

              '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-success totalamt" name=netamt_arr[]" id="saleprice_id' + product_id + '">' + rate * qty + '</span><input type="hidden" class="form-control saleprice" name="saleprice_arr[]" id="saleprice_idd' + product_id + '" value="' + rate * qty + '"></td>' +

              //  '<td style="text-align:left; vertical-align:middle; font-size:17px;"><center><name="remove" class="btnremove" data-id="'+pid+'"><span class="fas fa-trash" style="color:red"></span></center></td>'+

              '<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove" data-id="' + product_id + '"><span class="fas fa-trash"></span></center></td>' +


              '</tr>';

            $('.details').append(tr);
            calculate(0, 0);
            $('#txtpaid').val("");
            $('#txtdue').val("");





          }
        }
      });
      $("#txtbarcode_id").val("");

    }
  })







  $(function() {

    $('#txtbarcode_id').on('change', function() {
      var barcode = $("#txtbarcode_id").val();

      $.ajax({
        url: "getproduct.php",
        method: "get",
        datatype: "json",
        data: {
          id: barcode
        },
        success: function(data) {



          if (jQuery.inArray(data["pid"], productarr) !== -1) {

            var actualqty = parseInt($('#qty_id' + data["pid"]).val()) + 1;
            $('#qty_id' + data["pid"]).val(actualqty);

            var saleprice = parseInt(actualqty) * data["saleprice"];

            $('#saleprice_id' + data["pid"]).html(saleprice);
            $('#saleprice_idd' + data["pid"]).val(saleprice);

            // $("#txtbarcode_id").val("");

            calculate(0, 0);



          } else {

            addrow(data["pid"], data["product"], data["saleprice"], data["stock"], data["barcode"]);

            productarr.push(data["pid"]);

            // $("#txtbarcode_id").val("");

            function addrow(pid, product, saleprice, stock, barcode) {

              var tr = '<tr>' +

              '<input type="hidden" class="form-control barcode" name="barcode_arr[]" id="barcode_id' + barcode + '" value="' +barcode+ '"></td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><class="form-control product_c" name="product_arr[]"  <span class="badge badge-dark">' + product + '</span><input type="hidden" class="form-control pid" name="pid_arr[]" value="' + pid + '"><input type="hidden" class="form-control product" name="product_arr[]" value="' + product + '"> </td>' +

                '<td style="text-align:left;vertical-align:middle; font-size:17px;"><span class="badge badge-primary stocklbl" name="stock_arr[]" id="stock_id' + pid + '">' + stock + '<span><input type="hidden" class="form-control stock_C" name="stock_c_arr[]" id="stock_idd' + pid + '" value="' + stock + '"></td>' +

                '<td style="text-align:left;vertical-align:middle; font-size:17px;"><span class="badge badge-warning price" name="price_arr[]" id="price_id' + pid + '">' + saleprice + '<span><input type="hidden" class="form-control price_C" name="price_c_arr[]" id="price_idd' + pid + '" value="' + saleprice + '"></td>' +

                '<td><input type="text" class="form-control qty" name="quantity_arr[]" id="qty_id' + pid + '" value="' + 1 + '" size="1"></td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-success totalamt" name=netamt_arr[]" id="saleprice_id' + pid + '">' + saleprice + '</span><input type="hidden" class="form-control saleprice" name="saleprice_arr[]" id="saleprice_idd' + pid + '" value="' + saleprice + '"></td>' +

                //  '<td style="text-align:left; vertical-align:middle; font-size:17px;"><center><name="remove" class="btnremove" data-id="'+pid+'"><span class="fas fa-trash" style="color:red"></span></center></td>'+

                '<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove" data-id="' + pid + '"><span class="fas fa-trash"></span></center></td>' +


                '</tr>';

              $('.details').append(tr);
              calculate(0, 0);



            }
$("#txtbarcode_id").val("");


          }




        } // end of success fucntion
      }) // end of ajax request

    }) // end of onchange function
  }); // edn of main fucntion
 

  //search product 


  var productarr = [];
  $(function() {

    $('.select2').on('change', function() {
      var productid = $(".select2").val();

      $.ajax({
        url: "getproduct.php",
        method: "get",
        datatype: "json",
        data: {
          id: productid
        },
        success: function(data) {



          if (jQuery.inArray(data["pid"], productarr) !== -1) {

            var actualqty = parseInt($('#qty_id' + data["pid"]).val()) + 1;
            $('#qty_id' + data["pid"]).val(actualqty);

            var saleprice = parseInt(actualqty) * data["saleprice"];

            $('#saleprice_id' + data["pid"]).html(saleprice);
            $('#saleprice_idd' + data["pid"]).val(saleprice);

            // $("#txtbarcode_id").val("");

            calculate(0, 0);
          } else {


            addrow(data["pid"], data["product"], data["saleprice"], data["stock"], data["barcode"]);

            productarr.push(data["pid"]);

            // $("#txtbarcode_id").val("");

            function addrow(pid, product, saleprice, stock, barcode) {

              var tr = '<tr>' +

              '<input type="hidden" class="form-control barcode" name="barcode_arr[]" id="barcode_id' + barcode + '" value="' +barcode+ '">' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><class="form-control product_c" name="product_arr[]" <span class="badge badge-dark">' + product + '</span><input type="hidden" class="form-control pid" name="pid_arr[]" value="' + pid + '"><input type="hidden" class="form-control product" name="product_arr[]" value="' + product + '"> </td>' +

                '<td style="text-align:left;vertical-align:middle; font-size:17px;"><span class="badge badge-primary stocklbl" name="stock_arr[]" id="stock_id' + pid + '">' + stock + '<span><input type="hidden" class="form-control stock_C" name="stock_c_arr[]" id="stock_idd' + pid + '" value="' + stock + '"></td>' +

                '<td style="text-align:left;vertical-align:middle; font-size:17px;"><span class="badge badge-warning price" name="price_arr[]" id="price_id' + pid + '">' + saleprice + '<span><input type="hidden" class="form-control price_C" name="price_c_arr[]" id="price_idd' + pid + '" value="' + saleprice + '"></td>' +

                '<td><input type="text" class="form-control qty" name="quantity_arr[]" id="qty_id' + pid + '" value="' + 1 + '" size="1"></td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-success totalamt" name=netamt_arr[]" id="saleprice_id' + pid + '">' + saleprice + '</span><input type="hidden" class="form-control saleprice" name="saleprice_arr[]" id="saleprice_idd' + pid + '" value="' + saleprice + '"></td>' +

                //  '<td style="text-align:left; vertical-align:middle; font-size:17px;"><center><name="remove" class="btnremove" data-id="'+pid+'"><span class="fas fa-trash" style="color:red"></span></center></td>'+

                '<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove" data-id="' + pid + '"><span class="fas fa-trash"></span></center></td>' +


                '</tr>';

              $('.details').append(tr);

              calculate(0, 0);

            }

            $("#txtbarcode_id").val("");

          }




        }
      })

    })
  });









  $("#itemtable").delegate(".qty", "keyup change", function() {


    var quantity = $(this);
    var tr = $(this).parent().parent();

    if ((quantity.val() - 0) > (tr.find(".stock_C").val() - 0)) {

      Swal.fire("WARNING!", "SORRY! this much of quantity is NOT Available", "warning");

      quantity.val(1);

      tr.find(".totalamt").text(quantity.val() * tr.find(".price").text());

      tr.find(".saleprice").val(quantity.val() * tr.find(".price").text());
      calculate(0, 0);

    } else {

      tr.find(".totalamt").text(quantity.val() * tr.find(".price").text());

      tr.find(".saleprice").val(quantity.val() * tr.find(".price").text());
      calculate(0, 0);
    }
    


  });


  function calculate(dis, paid) {

    var subtotal = 0;
    var discount = dis;
    var sgst = 0;
    var cgst = 0;
    var total = 0;
    var paid_amt = paid;
    var due = 0;

    $(".saleprice").each(function() {

      subtotal = subtotal + $(this).val() * 1;


    });

    $("#txtsubtotal_id").val(subtotal.toFixed(2));

    sgst = parseFloat($("#txtsgst_id_p").val());

    cgst = parseFloat($("#txtcgst_id_p").val());

    discount = parseFloat($("#txtdiscount_p").val());

    sgst = sgst / 100;
    sgst = sgst * subtotal;

    cgst = cgst / 100;
    cgst = cgst * subtotal;

    discount = discount / 100;
    discount = discount * subtotal;

    $("#txtsgst_id_n").val(sgst.toFixed(2));

    $("#txtcgst_id_n").val(cgst.toFixed(2));

    $("#txtdiscount_n").val(discount.toFixed(1));

    total = sgst + cgst + subtotal - discount;
    due = total - paid_amt;

    $("#txttotal").val(total.toFixed(2));

    $("#txtdue").val(due.toFixed(2));


  } //calculate function

  //calculate function (continued)
  $("#txtdiscount_p").keyup(function() {
    var discount = $(this).val();
    calculate(discount, 0);
  });

  $("#txtpaid").keyup(function() {
    var paid = $(this).val();
    var discount = $("#txtdiscount_p").val();
    calculate(discount, paid);
  });

  $(document).on('click', '.btnremove', function() {
    var removed = $(this).attr("data-id");
    productarr = jQuery.grep(productarr, function(value) {
      return value != removed;
    });
    $(this).closest('tr').remove();
    calculate(0, 0); // Recalculate subtotal after removing an item
  });
</script>
