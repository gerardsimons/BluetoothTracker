package com.simons.bluetoothtracker.models;

public class MyBluetoothDevice {


    private String name = "<NO NAME>";
    private String address = "<NO ADDRESS>";
    private Integer latestRSSI = null;

    public MyBluetoothDevice(String name, String address) {
        this.name = name;
        this.address = address;
    }

    public MyBluetoothDevice(String name, String address, int latestRSSI) {
        this.address = address;
        this.name = name;
        this.latestRSSI = latestRSSI;
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

    public String toString() {
        String toString = "MyBlueToothDevice\nName: " + getName() + "\nAddress:" + getAddress() + "\nRSSI Value: " + latestRSSI;
        return toString;
    }

    public void setLatestRSSI(int latestRSSI) {
        this.latestRSSI = latestRSSI;
    }
}
