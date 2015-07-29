package com.simons.bletracker.controllers;

import com.simons.bletracker.models.Customer;
import com.simons.bletracker.models.IntegerBarcode;
import com.simons.bletracker.models.Order;
import com.simons.bletracker.models.OrderCase;
import com.simons.bletracker.remote.ServerAPI;

import java.util.ArrayList;

/**
 * Created by gerard on 23/07/15.
 *
 * This class caches data, and determines when new Server calls are necessary for persistent storage
 */
public class DataController {

    private ArrayList<OrderCase> orderCases;
    private ArrayList<Order> orders;
    private ServerAPI serverAPI;

    private static DataController instance;

    private static final int[] BARCODE_COMPONENT_LENGTHS = {4,6,10,2};

    private DataController() {
        serverAPI = ServerAPI.GetInstance();
    }

    public static DataController GetInstance() {
        if(instance == null) {
            instance = new DataController();
        }
        return instance;
    }

    private Order getOrderWithID(int id) {
        for(Order order : orders) {
            if(order.getID() == id) {
                return order;
            }
        }
        return null;
    }

    public void newOrderCaseScanned(String orderCaseCode) {
        //Convert to barcode
        IntegerBarcode barcode = new IntegerBarcode(orderCaseCode,BARCODE_COMPONENT_LENGTHS);

        //Extract the IDs from the barcode
        int customerId = barcode.getComponent(1);
        int orderId = barcode.getComponent(2);
        int orderCaseId = barcode.getComponent(3);

        //Check if order already created
        Order existingOrder = getOrderWithID(orderId);
        if(existingOrder == null) { //No existing order found, so create in database
            Order newOrder = new Order(orderId,new Customer(customerId));

            serverAPI.
        }
    }

    public void newGPSData() {

    }

    public void onRSSIReceived() {

    }
}
