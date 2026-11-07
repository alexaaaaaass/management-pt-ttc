/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Tools;

import java.awt.*;
import java.awt.event.MouseEvent;
import java.awt.event.MouseListener;
import javax.swing.*;
import javax.swing.plaf.metal.MetalIconFactory;
import Main.MenuAdmin;

/**
 * @author 6dc
 *
 * A class which creates a JTabbedPane and auto sets a close button when you add a tab
 */
public class Closingtab extends JTabbedPane {

    public Closingtab() {
        super();
    }

    /* Override Addtab in order to add the close Button everytime */
    @Override
    public void addTab(String title, Icon icon, Component component, String tip) {
        super.addTab(title, icon, component, tip);
        int count = this.getTabCount() - 1;
        setTabComponentAt(count, new CloseButtonTab(component, title, icon));
    }

    @Override
    public void addTab(String title, Icon icon, Component component) {
        addTab(title, icon, component, null);
    }

    @Override
    public void addTab(String title, Component component) {
        addTab(title, null, component);
    }

    /* addTabNoExit */
    public void addTabNoExit(String title, Icon icon, Component component, String tip) {
        super.addTab(title, icon, component, tip);
    }

    public void addTabNoExit(String title, Icon icon, Component component) {
        addTabNoExit(title, icon, component, null);
    }

    public void addTabNoExit(String title, Component component) {
        addTabNoExit(title, null, component);
    }

    /* Button */
    public class CloseButtonTab extends JPanel {
        private Component tab;

        public CloseButtonTab(final Component tab, String title, Icon icon) {
            this.tab = tab;
            setOpaque(false);
            FlowLayout flowLayout = new FlowLayout(FlowLayout.CENTER, 3, 3);
            setLayout(flowLayout);
            JLabel jLabel = new JLabel(title);
            jLabel.setIcon(icon);
            add(jLabel);
            JButton button = new JButton(MetalIconFactory.getInternalFrameCloseIcon(5));
            button.setMargin(new Insets(0, 0, 0, 0));
            button.addMouseListener(new CloseListener(tab));
            add(button);
        }
    }
    /* ClickListener */
    public class CloseListener implements MouseListener
    {
        private Component tab;

        public CloseListener(Component tab){
            
            this.tab=tab;
        }

        @Override
        public void mouseClicked(MouseEvent e) {
            System.out.println(e);
            System.out.println(tab);
            if(e.getSource() instanceof JButton){
                JButton clickedButton = (JButton) e.getSource();
                try {
                    //WRAP TIPE
                    JTabbedPane tabbedPane = (JTabbedPane) clickedButton.getParent().getParent().getParent();
//                    JTabbedPane tabbedPane ;
                    tabbedPane.remove(tab);
                } catch (Exception ee){
                    //SCROLL TYPE
                    for(int i=0; i<MenuAdmin.formView.getTabCount(); i++){
                        if(MenuAdmin.formView.getComponentAt(i).getClass().equals(tab.getClass())) {
                            MenuAdmin.formView.remove(i);
                            break;
                        }
                    }
                    //formView.getComponentAt(formView.getSelectedIndex()).getClass()
                    //ee.printStackTrace();
//                    JTabbedPane tabbedPane = (JTabbedPane) clickedButton.getParent();
//                    tabbedPane.remove(0);
                    //MenuAdmin.formView.remove(MenuAdmin.formView.getSelectedIndex());
                }
            }
        }

        @Override
        public void mousePressed(MouseEvent e) {}

        @Override
        public void mouseReleased(MouseEvent e) {}

        @Override
        public void mouseEntered(MouseEvent e) {
            if(e.getSource() instanceof JButton){
                JButton clickedButton = (JButton) e.getSource();
             //   clickedButton.setBorder(BorderFactory.createLineBorder(Color.DARK_GRAY,3));
            }
        }

        @Override
        public void mouseExited(MouseEvent e) {
            if(e.getSource() instanceof JButton){
                JButton clickedButton = (JButton) e.getSource();
             //   clickedButton.setBorder(BorderFactory.createLineBorder(Color.LIGHT_GRAY,3));
            }
        }
    }
}