<div class="tabbable tabbable-bordered">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab1" data-toggle="tab"><?php echo $lang['admin_invoices_summary']; ?></a></li>
        <li><a href="#tab2" data-toggle="tab"><?php echo $lang['admin_invoices_edit']; ?></a></li>
        <li><a href="#tab4" data-toggle="tab"><?php echo $lang['admin_invoices_add_payment']; ?></a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab1">
            <div class="row">
                <div class="col-md-9">
                    <table class="table table-bordered table-condensed">
                        <tr>
                            <td><?php echo $lang['admin_invoices_date']; ?></td>
                            <td><?php echo $date; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $lang['admin_invoices_date_due']; ?></td>
                            <td><?php echo $date_due; ?></td>
                        </tr>
                        <?php if($status == 'paid') { ?>
                        <tr>
                            <td><?php echo $lang['admin_invoices_date_paid']; ?></td>
                            <td><?php echo $date_paid; ?></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td><?php echo $lang['admin_invoices_total']; ?></td>
                            <td><?php echo $total; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $lang['admin_invoices_balance']; ?></td>
                            <td>
                                <?php if($balance < 0) { ?>
                                    <span class="label label-warning"><?php echo $balance_formatted; ?></span>
                                <?php } elseif($balance == 0) { ?>
                                    <span class="label label-success"><?php echo $balance_formatted; ?></span>
                                <?php } else { ?>
                                    <span class="label label-danger"><?php echo $balance_formatted; ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $lang['admin_invoices_payment_method']; ?></td>
                            <td><?php echo $gateway_id; ?></td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-9" style="text-align: center">
                    <p>
                        <span class="label label-<?php if($status == 'unpaid') { ?>warning<?php } elseif($status == 'paid') { ?>success<?php } ?> label-xl"><?php echo $lang['admin_invoices_'.$status]; ?></span>
                    </p>
                    <p>
                        <a class="btn btn-default" href="JavaScript:newWindow('admin_invoices.php?action=print&id=<?php echo $id; ?>','popup',650,600,'')"><i class="glyphicon glyphicon-print"></i> <?php echo $lang['admin_invoices_print']; ?></a>
                        <a class="btn btn-default" href="admin_invoices.php?action=pdf&id=<?php echo $id; ?>"><i class="glyphicon glyphicon-file"></i> <?php echo $lang['admin_invoices_download_pdf']; ?></a>
                    </p>
                    <p>
                        <a class="btn btn-success btn-sm" href="admin_invoices.php?action=paid&id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>"><?php echo $lang['admin_invoices_mark']; ?> <?php echo $lang['admin_invoices_paid']; ?></a>
                        <a class="btn btn-warning btn-sm" href="admin_invoices.php?action=unpaid&id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>"><?php echo $lang['admin_invoices_mark']; ?> <?php echo $lang['admin_invoices_unpaid']; ?></a>
                        <a class="btn btn-default btn-sm" href="admin_invoices.php?action=canceled&id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>"><?php echo $lang['admin_invoices_mark']; ?> <?php echo $lang['admin_invoices_canceled']; ?></a>
                        <a class="btn btn-danger btn-sm" href="admin_invoices.php?action=delete&id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>"><?php echo $lang['admin_delete']; ?></a>
                    </p>
                    <p>
                        <?php echo $email_form->getFormOpenHTML(array('class'=>'form-inline')); ?>
                            <?php echo $email_form->getFieldHTML('email'); ?>
                            <?php echo $email_form->getFieldHTML('email_submit'); ?>
                        <?php echo $email_form->getFormCloseHTML(); ?>
                    </p>
                </div>
            </div>
            <?php if(!empty($notes)) { ?>
                <div class="alert alert-info">
                    <h4><?php echo $lang['admin_invoices_notes']; ?></h4>
                    <?php echo $notes; ?>
                </div>
            <?php } ?>
            <h3><?php echo $lang['admin_invoices_items']; ?></h3>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php echo $lang['admin_invoices_description']; ?></td>
                        <th><?php echo $lang['admin_invoices_total']; ?></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $description; ?></td>
                        <td><?php echo $subtotal; ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: right"><?php echo $lang['admin_invoices_subtotal']; ?>:</td>
                        <td><?php echo $subtotal; ?></td>
                    </tr>
                    <?php if($tax) { ?>
                    <tr>
                        <td style="text-align: right"><?php echo $lang['admin_invoices_tax']; ?>:</td>
                        <td><?php echo $tax; ?></td>
                    </tr>
                    <?php } ?>
                    <?php if($tax2) { ?>
                        <tr>
                            <td style="text-align: right"><?php echo $lang['admin_invoices_tax']; ?>:</td>
                            <td><?php echo $tax2; ?></td>
                        </tr>
                    <?php } ?>
                    </tr>
                        <td style="text-align: right"><?php echo $lang['admin_invoices_total']; ?>:</td>
                        <td><?php echo $total; ?></td>
                    </tr>
                </tbody>
            </table>

            <h3><?php echo $lang['admin_transactions']; ?></h3>
            <?php echo $transactions; ?>
        </div>
        <div class="tab-pane" id="tab2">
            <?php echo $form; ?>
        </div>
        <div class="tab-pane" id="tab4">
            <?php echo $payment_form; ?>
        </div>
    </div>
</div>