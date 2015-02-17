package com.simons.bluetoothtracker.models;

public class MyBluetoothDevice {


    private String name = "<NO NAME>";
    private String address = "<NO ADDRESS>";
    private Integer latestRSSI = null;
    private ProductType productType;

    public MyBluetoothDevice(String name, String address, ProductType productType) {
        this.name = name;
        this.address = address;
        this.productType = productType;
    }

    public MyBluetoothDevice(String name, String address, int latestRSSI, ProductType productType) {
        this.address = address;
        this.name = name;
        this.latestRSSI = latestRSSI;
        this.productType = productType;
    }

    public String getAddress() {
        return address;
    }

    public boolean equals(Object o) {
        if (o instanceof MyBluetoothDevice) {
            MyBluetoothDevice other = (MyBluetoothDevice) o;
            if (other.getAddress().equals(getAddress())) {
                return true;
            }
        }
        return false;
    }

    public String getName() {
        return name;
    }

    public Integer getLatestRSSI() {
        return latestRSSI;
    }

    public ProductType getProductType() { return productType; }

    public String toString() {
        String toString = "MyBlueToothDevice\nName: " + getName() + "\nAddress:" + getAddress() + "\nRSSI Value: " + latestRSSI;
        return toString;
    }

    public void setLatestRSSI(int latestRSSI) {
        this.latestRSSI = latestRSSI;
    }
}
