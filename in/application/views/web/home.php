<?php

    $data = $invoice->row();
    $session_id = $data->invoice_session; //get by invoice table

    $this->db->where('user_session',$session_id);
    $query = $this->db->get('items');


    if ($query->num_rows()>0) {
        $item_query = $query;
    }else{
        $insert = array('user_session' => $session_id,'invoice_id' => $data->id );
        $this->db->insert('items',$insert);

        $this->db->where('user_session',$session_id);
        $this->db->order_by('id','desc');
        $this->db->limit(1);
        $item_query = $this->db->get('items');
    }

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Invoice Generator</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/style.css">
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-3.1.1.slim.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-3.2.1.min.js"></script>
  </head>
  <body>

    <form action="<?php echo base_url(); ?>home/create_pdf" method="POST" enctype="multipart/form-data">
        <header>
            <div class="container">
                <div class="offset-md-1 col-md-10 col-sm-12">
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="left-header">
                                <h1>Invoice</h1>
                                <div class="form-group">
                                        <label>Invoice Num.</label>
                                        <input type="text" name="invoice_no" placeholder="01" id="" required="1">
                                </div>
                                <div class="form-group">
                                        <label>Date</label>
                                        <input type="text" name="dated" placeholder="<?php echo date("Y/m/d"); ?>" id="datepicker" required="1">
                                </div>
                                <div class="form-group" style="margin-bottom: 30px;">
                                        <label>Due Date</label>
                                        <input type="text" name="due_dated" placeholder="<?php echo date("Y/m/d"); ?>" id="datepicker-due">
                                </div>
                                <div class="form-group">
                                        <label>Bill to</label>
                                </div>
                                <div class="form-group">
                                       <textarea name="bill_to" id="input" class="bill-to-class" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="image-layout">
                                <div id="targetOuter">
                                    <div id="targetLayer"></div>
                                    <div class="icon-choose-image" >
                                      <input name="userfile" id="userImage" type="file" class="inputFile" onChange="showPreview(this);" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </header>
        <section class="content-section">
            <div class="container">
                <div class="offset-md-1 col-md-10 col-sm-12">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="30%">Item Description</th>
                                    <th width="10%">Qty</th>
                                    <th width="20%">Price</th>
                                    <th width="10%">Discount %</th>
                                    <th width="20%">Subtotal</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($item_query->result_array() as $value) {
                                    $item_id = $value['id'];
                                    $user_sesison = $value['user_session'];
                                    $price = $value['price'];
                                    $item_name = $value['item_name'];
                                    $qty = $value['qty'];
                                    $discount = $value['discount'];
                                    $subtotal = $value['subtotal'];
                                ?>
                                    <tr id="item<?php echo $item_id; ?>">
                                        <td><input id="item_name<?php echo $item_id; ?>" data-id="<?php echo $item_id; ?>" placeholder="Item name" type="text" value="<?php echo $item_name; ?>" size="25"></td>
                                        <td><input id="qty<?php echo $item_id; ?>" data-id="<?php echo $item_id; ?>" onblur="return qty(this);" type="text" value="<?php echo $qty; ?>" size="10"></td>
                                        <td><input id="price<?php echo $item_id; ?>" data-id="<?php echo $item_id; ?>" onblur="return price(this);" type="text" value="<?php echo $price; ?>" size="10"></td>
                                        <td><input id="discount<?php echo $item_id; ?>" data-id="<?php echo $item_id; ?>" onblur="return discount(this);" type="text" value="<?php echo $discount; ?>" size="10"></td>
                                        <td><input id="subtotal<?php echo $item_id; ?>" data-id="<?php echo $item_id; ?>" type="text" value="<?php echo $subtotal; ?>" size="10"></td>
                                        <td class="for-delete-td">
                                            <button class="btn btn-sm btn-default" value="<?php echo $item_id; ?>" onclick="return delete_items(this);" type="button">x</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tbody id="trMore">
                        </table>
                    </div>

                    <div class="new-line">
                        <button class="btn btn-default btn-sm" value="<?php echo $data->id; ?>" onclick="return new_line(this);" type="button">New Line</button>
                    </div>

                    <div class="invoice-sub-total text-right">
                        <?php
                        $this->db->where('user_session',$session_id);
                        $this->db->select_sum("subtotal");
                        $subtotal_query = $this->db->get("items");
                        if ($subtotal_query->num_rows()>0) {
                          $get_total = $subtotal_query->row()->subtotal;
                          $total = number_format($get_total,2);
                        }
                        ?>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>Subtotal</label>
                                <input type="text" name="sub_total" value="<?php echo $total; ?>" id="total" required="1">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>Tax %</label>
                                <input type="text" name="tax" placeholder="0" id="tax" onblur="return added_tax(this);">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>Total Amount</label>
                                <input type="text" name="total_krw" placeholder="0.00" id="total-krw">
                            </div>
                        </div>
                    </div>

                    <div class="invoice-notes">
                        <div class="row">
                            <div class="col-md-8 col-sm-12">
                                <div class="form-group" style="margin: 5px 0">
                                    <label><strong>Notes</strong></label>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" value="<?php echo $data->id; ?>" name="invoice_id">
                                   <textarea name="notes" rows="2" id="input" class="note-class" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="invoice-submit text-right">
                                    <button class="btn btn-default" type="submit">Create Invoice</button>
                                    <a href="https://thirdhandbd.com/" class="btn btn-default">Back to Home</a>
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    
                </div>
            </div>
        </section>
    </form>
    
    <!-- jQuery first, then Tether, then Bootstrap JS. -->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/tether.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/functions.js"></script>
  </body>
</html>