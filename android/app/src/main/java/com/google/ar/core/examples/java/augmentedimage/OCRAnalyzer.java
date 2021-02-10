package com.google.ar.core.examples.java.augmentedimage;

import android.content.Context;
import android.media.Image;

import com.google.android.gms.tasks.OnFailureListener;
import com.google.android.gms.tasks.OnSuccessListener;
import com.google.android.gms.tasks.Task;
import com.google.ar.core.Frame;
import com.google.ar.core.exceptions.NotYetAvailableException;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.google.gson.stream.JsonReader;
import com.google.mlkit.vision.common.InputImage;
import com.google.mlkit.vision.text.Text;
import com.google.mlkit.vision.text.TextRecognition;
import com.google.mlkit.vision.text.TextRecognizer;

import android.net.Uri;
import android.os.Build;
import android.util.Log;
import android.widget.Toast;

import org.json.JSONObject;

import java.io.File;
import java.io.FileDescriptor;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.lang.reflect.Type;
import java.net.URI;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;
import java.util.Map.Entry;
import java.util.Scanner;

import androidx.annotation.NonNull;
import androidx.annotation.RequiresApi;
import me.xdrop.fuzzywuzzy.FuzzySearch;

public class OCRAnalyzer {
    private TextRecognizer recognizer = TextRecognition.getClient();
    // Only do one image at a time
    private boolean blocked = false;
    private HashMap<Integer, String> textDatabase;
    private Context context;
    private Toast lastToast;

    public OCRAnalyzer(FileDescriptor textDatabase, Context context) {

        String text = "{\"41\": \"PETERSON-AND-DAVIE 07-ch01-000-069-9780123850591 2011/11/1 9:29 Page 9 #10\\n\\n1.2 Requirements\\n\\n9\\n\\n(a)\\n\\n(b)\\n\\nI FIGURE 1.2 Direct links: (a) point-to-point; (b) multiple-access.\\n\\nfollowing two examples of how a collection of computers can be indirectly\\nconnected.\\n\\nFigure 1.3 shows a set of nodes, each of which is attached to one or\\nmore point-to-point links. Those nodes that are attached to at least two\\nlinks run software that forwards data received on one link out on another.\\nIf organized in a systematic way, these forwarding nodes form a switched\\nnetwork. There are numerous types of switched networks, of which the\\ntwo most common are circuit switched and packet switched. The former\\nis most notably employed by the telephone system, while the latter is\\nused for the overwhelming majority of computer networks and will be\\nthe focus of this book. (Circuit switching is, however, making a bit of a\\ncomeback in the optical networking realm, which turns out to be impor-\\ntant as demand for network capacity constantly grows.) The important\\nfeature of packet-switched networks is that the nodes in such a network\\nsend discrete blocks of data to each other. Think of these blocks of data as\\ncorresponding to some piece of application data such as a le, a piece of\\nemail, or an image. We call each block of data either a packet or a message,\\nand for now we use these terms interchangeably; we discuss the reason\\nthey are not always the same in Section 1.2.3.\\n\\nPacket-switched networks typically use a strategy called store-and-\\nforward. As the name suggests, each node in a store-and-forward network\\nrst receives a complete packet over some link, stores the packet in its\\ninternal memory, and then forwards the complete packet to the next\\n\\n\\f\", \"42\": \"PETERSON-AND-DAVIE 07-ch01-000-069-9780123850591 2011/11/1 9:29 Page 10 #11\\n\\n10\\n\\nCHAPTER 1 Foundation\\n\\nI FIGURE 1.3 Switched network.\\n\\nnode. In contrast, a circuit-switched network rst establishes a dedicated\\ncircuit across a sequence of links and then allows the source node to send\\na stream of bits across this circuit to a destination node. The major rea-\\nson for using packet switching rather than circuit switching in a computer\\nnetwork is efciency, discussed in the next subsection.\\n\\nThe cloud in Figure 1.3 distinguishes between the nodes on the inside\\nthat implement the network (they are commonly called switches, and\\ntheir primary function is to store and forward packets) and the nodes\\non the outside of the cloud that use the network (they are commonly\\ncalled hosts, and they support users and run application programs). Also\\nnote that the cloud in Figure 1.3 is one of the most important icons of\\ncomputer networking. In general, we use a cloud to denote any type of\\nnetwork, whether it is a single point-to-point link, a multiple-access link,\\nor a switched network. Thus, whenever you see a cloud used in a gure,\\n\\n\\f\", \"43\": \"PETERSON-AND-DAVIE 07-ch01-000-069-9780123850591 2011/11/1 9:29 Page 11 #12\\n\\n1.2 Requirements\\n\\n11\\n\\nI FIGURE 1.4 Interconnection of networks.\\n\\nyou can think of it as a placeholder for any of the networking technologies\\ncovered in this book.2\\n\\nA second way in which a set of computers can be indirectly connected\\nis shown in Figure 1.4. In this situation, a set of independent networks\\n(clouds) are interconnected to form an internetwork, or internet for short.\\nWe adopt the Internets convention of referring to a generic internet-\\nwork of networks as a lowercase i internet, and the currently operational\\nTCP/IP Internet as the capital I Internet. A node that is connected to two\\nor more networks is commonly called a router or gateway, and it plays\\nmuch the same role as a switchit forwards messages from one net-\\nwork to another. Note that an internet can itself be viewed as another\\nkind of network, which means that an internet can be built from an\\ninterconnection of internets. Thus, we can recursively build arbitrarily\\nlarge networks by interconnecting clouds to form larger clouds. It can\\n\\n2Interestingly, the use of clouds in this way predates the term cloud computing by at\\nleast a couple of decades, but there is a connection between these two usages, which\\nwell discuss later.\\n\\n\\f\", \"44\": \"PETERSON-AND-DAVIE 07-ch01-000-069-9780123850591 2011/11/1 9:29 Page 12 #13\\n\\n12\\n\\nCHAPTER 1 Foundation\\n\\nreasonably be argued that this idea of interconnecting widely differing\\nnetworks was the fundamental innovation of the Internet and that the\\nsuccessful growth of the Internet to global size and billions of nodes\\nwas the result of some very good design decisions by the early Internet\\narchitects, which we will discuss later.\\n\\nJust because a set of hosts are directly or indirectly connected to each\\nother does not mean that we have succeeded in providing host-to-host\\nconnectivity. The nal requirement is that each node must be able to\\nsay which of the other nodes on the network it wants to communicate\\nwith. This is done by assigning an address to each node. An address is a\\nbyte string that identies a node; that is, the network can use a nodes\\naddress to distinguish it from the other nodes connected to the network.\\nWhen a source node wants the network to deliver a message to a certain\\ndestination node, it species the address of the destination node. If the\\nsending and receiving nodes are not directly connected, then the switches\\nand routers of the network use this address to decide how to forward the\\nmessage toward the destination. The process of determining systemati-\\ncally how to forward messages toward the destination node based on its\\naddress is called routing.\\n\\nThis brief introduction to addressing and routing has presumed that\\nthe source node wants to send a message to a single destination node\\n(unicast). While this is the most common scenario, it is also possible that\\nthe source node might want to broadcast a message to all the nodes on the\\nnetwork. Or, a source node might want to send a message to some subset\\nof the other nodes but not all of them, a situation called multicast. Thus,\\nin addition to node-specic addresses, another requirement of a network\\nis that it support multicast and broadcast addresses.\\n\\nThe main idea to take away from this discussion is that we can define a network\\nrecursively as consisting of two or more nodes connected by a physical link, or\\nas two or more networks connected by a node. In other words, a network can be\\nconstructed from a nesting of networks, where at the bottom level, the network is\\nimplemented by some physical medium. Among the key challenges in providing\\nnetwork connectivity are the definition of an address for each node that is reach-\\nable on the network (including support for broadcast and multicast), and the\\nuse of such addresses to forward messages toward the appropriate destination\\nnode(s).\\n\\n\\f\", \"45\": \"PETERSON-AND-DAVIE 07-ch01-000-069-9780123850591 2011/11/1 9:29 Page 13 #14\\n\\n1.2 Requirements\\n\\n13\\n\\n1.2.3 Cost-Effective Resource Sharing\\n\\nAs stated above, this book focuses on packet-switched networks. This\\nsection explains the key requirement of computer networksefciency\\nthat leads us to packet switching as the strategy of choice.\\n\\nGiven a collection of nodes indirectly connected by a nesting of net-\\nworks, it is possible for any pair of hosts to send messages to each other\\nacross a sequence of links and nodes. Of course, we want to do more than\\nsupport just one pair of communicating hostswe want to provide all\\npairs of hosts with the ability to exchange messages. The question, then,\\nis how do all the hosts that want to communicate share the network, espe-\\ncially if they want to use it at the same time? And, as if that problem isnt\\nhard enough, how do several hosts share the same link when they all want\\nto use it at the same time?\\n\\nTo understand how hosts share a network, we need to introduce a fun-\\ndamental concept, multiplexing, which means that a system resource is\\nshared among multiple users. At an intuitive level, multiplexing can be\\nexplained by analogy to a timesharing computer system, where a single\\nphysical processor is shared (multiplexed) among multiple jobs, each of\\nwhich believes it has its own private processor. Similarly, data being sent\\nby multiple users can be multiplexed over the physical links that make up\\na network.\\n\\nTo see how this might work, consider the simple network illustrated in\\nFigure 1.5, where the three hosts on the left side of the network (senders\\nS1S3) are sending data to the three hosts on the right (receivers R1R3)\\nby sharing a switched network that contains only one physical link. (For\\nsimplicity, assume that host S1 is sending data to host R1, and so on.)\\nIn this situation, three ows of datacorresponding to the three pairs of\\nhostsare multiplexed onto a single physical link by switch 1 and then\\ndemultiplexed back into separate ows by switch 2. Note that we are being\\nintentionally vague about exactly what a ow of data corresponds to.\\nFor the purposes of this discussion, assume that each host on the left\\nhas a large supply of data that it wants to send to its counterpart on the\\nright.\\n\\nThere are several different methods for multiplexing multiple ows\\nonto one physical link. One common method is synchronous time-\\ndivision multiplexing (STDM). The idea of STDM is to divide time into\\nequal-sized quanta and, in a round-robin fashion, give each ow a chance\\n\\n\\f\"}";
//        try {
//            text = new Scanner(new File(Uri.parse("file:///android_assets/computernetworks.json"))).useDelimiter("\\A").next();
//        } catch (FileNotFoundException e) {
//            e.printStackTrace();
//        }
//        new Scanner(textDatabase)
//        JsonReader reader = new JsonReader(new FileReader(textDatabase));
        Type type = new TypeToken<HashMap<Integer, String>>() {}.getType() ; // wtf

        this.textDatabase = new Gson().fromJson(text, type);
        if (this.textDatabase.isEmpty()) {
            Log.e("[OCRAnalyzer]", "Text database empty!" + textDatabase.toString());
        }

        this.context = context;

    }

    public void matchText(Text text) {
        HashMap<Integer, Integer> scores = new HashMap<>();
        for (Entry<Integer, String> entry : textDatabase.entrySet()) {
            // TODO: Improve search
            Integer score = FuzzySearch.ratio(text.getText(), entry.getValue());
            scores.put(entry.getKey(), score);
        }
        Integer bestMatch = Collections.max(scores.entrySet(), Map.Entry.comparingByValue()).getKey();
        Integer bestScore = scores.get(bestMatch);

        Log.i("[OCRAnalyzer]", "Best match: page " + bestMatch.toString() + " score " + bestScore.toString());
        Log.d("[OCRAnalyzer]", scores.toString());
        if (bestScore > 60) {
            if (lastToast != null) {
                lastToast.cancel();
            }
            lastToast = Toast.makeText(
                    context.getApplicationContext(), "MATCHED page " + bestMatch.toString() + " score " + bestScore.toString(), Toast.LENGTH_SHORT);
            lastToast.show();
        }
    }

    public void analyze(Frame frame) {
//        Log.i("", Integer.toString(image.getHeight()));
        Image image;

        // We only process one image at a time, even if ARCore takes many more
        // If we are mid-processing, we drop future requests
        if (blocked) {
//            image.close();
            return;
        } else {
            try {
                image = frame.acquireCameraImage();
            } catch (NotYetAvailableException e) {
                Log.w("[OCRAnalyzer]", "NotYetAvailableException");
                return;
            }
            blocked = true;
        }

        // Note: hard-coded vertical orientation. Might not work on other devices??
        InputImage inputImage = InputImage.fromMediaImage(image, 90);

        Task<Text> result = recognizer.process(inputImage)
                .addOnSuccessListener(new OnSuccessListener<Text>() {
                    @Override
                    public void onSuccess(Text text) {
                        Log.i("[OCRAnalyzer]", "Detected Text " + text.getText().length());
                        image.close();

                        if (text.getText().length() > 250) {
                            long t = System.currentTimeMillis();
                            matchText(text);
                            Log.v("[OCRAnalyzer]", "Search took " + Long.toString(System.currentTimeMillis() - t));
                        }

                        blocked = false;
                    }
                })
                .addOnFailureListener(new OnFailureListener() {
                    @Override
                    public void onFailure(@NonNull Exception e) {
                        Log.e("[OCRAnalyzer]", "Failed: " + e.toString());
                        image.close();
                        blocked = false;
                    }
                });
    }
}
