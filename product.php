<?php
  $page_title = 'Danh sách Sản phẩm & NVL';
  require_once('includes/load.php');
  page_require_level(2);
  
  $search_term = isset($_GET['search']) ? $_GET['search'] : '';
  $all_items = find_all_products_and_materials($search_term);

?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading clearfix">
          <div class="pull-left">
            <form class="form-inline" action="products.php" method="get">
              <div class="form-group">
                <input type="text" class="form-control" name="search" placeholder="Tìm theo Tên hoặc SKU" value="<?php echo remove_junk($search_term); ?>">
              </div>
              <button type="submit" class="btn btn-default">Tìm kiếm</button>
            </form>
          </div>
          <div class="pull-right">
            <a href="add_product.php" class="btn btn-primary">Thêm mới</a>
          </div>
        </div>
        <div class="panel-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th> Hình ảnh </th>
                <th> Tên Sản phẩm/NVL </th>
                <th class="text-center" style="width: 10%;"> Mã SKU </th>
                <th class="text-center" style="width: 10%;"> Đơn vị tính </th>
                <th class="text-center" style="width: 15%;"> Loại </th>
                <th class="text-center" style="width: 10%;"> Giá bán </th>
                <th class="text-center" style="width: 10%;"> Tồn kho </th>
                <th class="text-center" style="width: 5%;"> Chi tiết </th>
                <th class="text-center" style="width: 100px;"> Hành động </th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($all_items as $item):?>
              <tr>
                <td class="text-center"><?php echo count_id();?></td>
                <td>
                  <?php if($item['image']): ?>
                    <img src="uploads/products/<?php echo $item['image']; ?>" class="img-avatar" alt="hình ảnh">
                  <?php else: ?>
                    <img src="uploads/products/no_image.png" class="img-avatar" alt="không có ảnh">
                  <?php endif; ?>
                </td>
                <td> <?php echo remove_junk($item['name']); ?></td>
                <td class="text-center"> <?php echo remove_junk($item['sku']); ?></td>
                <td class="text-center"> <?php echo remove_junk($item['unit']); ?></td>
                <td class="text-center"> <?php echo remove_junk($item['item_type']); ?></td>
                <td class="text-center">
                    <?php echo ($item['item_type'] == 'Thành phẩm') ? remove_junk($item['sale_price']) : 'N/A'; ?>
                </td>

                <?php
                  $item_type_for_db = ($item['item_type'] == 'Thành phẩm') ? 'product' : 'raw_material';
                  
                  $total_stock = calculate_total_stock_for_item($item['id'], $item_type_for_db);
                  $inventory_details = get_inventory_details_for_item($item['id'], $item_type_for_db);
                ?>

                <td class="text-center">
                    <?php echo $total_stock; ?>
                </td>

                <td class="text-center">
                  <?php if ($total_stock > 0): ?>
                  <button type="button" class="btn btn-xs btn-info view-details-btn" 
                          data-toggle="modal" 
                          data-target="#inventoryModal"
                          data-item-name="<?php echo remove_junk($item['name']); ?>"
                          data-inventory='<?php echo json_encode($inventory_details); ?>'>
                      Xem
                  </button>
                  <?php endif; ?>
                </td>
                
                <td class="text-center">
                  <div class="btn-group">
                    <?php // Bây giờ chỉ cần dùng lại biến đã định nghĩa ở trên ?>
                    <a href="edit_product.php?id=<?php echo (int)$item['id'];?>&type=<?php echo $item_type_for_db; ?>" class="btn btn-info btn-xs"  title="Sửa" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a>
                     <a href="delete_product.php?id=<?php echo (int)$item['id'];?>&type=<?php echo $item_type_for_db; ?>" class="btn btn-danger btn-xs"  title="Xóa" data-toggle="tooltip" onclick="return confirm('Bạn có chắc chắn muốn xóa mục này?');">
                      <span class="glyphicon glyphicon-trash"></span>
                    </a>
                  </div>
                </td>
              </tr>
             <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal Chi tiết Tồn kho -->
  <div class="modal fade" id="inventoryModal" tabindex="-1" role="dialog" aria-labelledby="inventoryModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="inventoryModalLabel">Chi tiết Tồn kho</h4>
        </div>
        <div class="modal-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Kho hàng</th>
                <th>Vị trí</th>
                <th class="text-right">Số lượng</th>
              </tr>
            </thead>
            <tbody id="inventoryDetailsBody">
             <script type="text/javascript">
              $(document).on("click", ".view-details-btn", function () {
                  var itemName = $(this).data('item-name');
                  var inventoryData = $(this).data('inventory');              
                  // Cập nhật tiêu đề Modal
                  $("#inventoryModalLabel").html("Chi tiết tồn kho cho: <strong>" + itemName + "</strong>");
                  
                  // Xóa nội dung cũ và xây dựng bảng mới
                  var tableBody = $("#inventoryDetailsBody");
                  tableBody.empty(); 

                  if (inventoryData && inventoryData.length > 0) { // Thêm kiểm tra inventoryData không null
                      $.each(inventoryData, function(index, item) {
                          var row = '<tr>';
                          row += '<td>' + item.warehouse_name + '</td>';
                          row += '<td>' + item.location_code + '</td>';
                          row += '<td class="text-right">' + item.quantity + '</td>';
                          row += '</tr>';
                          tableBody.append(row);
                      });
                  } else {
                      tableBody.append('<tr><td colspan="3" class="text-center">Không có dữ liệu tồn kho chi tiết.</td></tr>');
                  }
              });
              </script>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
        </div>
      </div>
    </div>
  </div>
<?php include_once('layouts/footer.php'); ?>