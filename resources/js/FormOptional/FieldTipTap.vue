<template>
<div>
    <label v-if="label">{{ label }}</label>
    <editor :extensions="extensions" @update="updateModel" @onFocus="hideDropDowns" ref="editor">
      <div class="menubar" slot="menubar" slot-scope="{ nodes, marks }">
        <div v-if="nodes && marks">
          <div v-if="linkUrl" class="linkfield" :style="styleLinkContainer">
            <input
              type="text"
              @focus="writingUrl=true"
              placeholder="https://"
              v-model="linkUrl"
              @keyup.enter.prevent="confirmLinkMark(marks, nodes)"
            >
            <button class="btn btn-secondary btn-xs" @click.prevent="confirmLinkMark(marks, nodes)">
              <span class="dashicons dashicons-yes"></span>
            </button>
            <button class="btn btn-secondary btn-xs" @click.prevent="removeLinkMark(marks, nodes)">
              <span class="dashicons dashicons-editor-unlink"></span>
            </button>
          </div>
          <div>
            <div class="d-flex flex-wrap">
              <button
                class="btn btn-secondary btn-xs"
                :class="{ 'active': getActiveState(marks, nodes, button.mark) }"
                @click.prevent="getCommand(marks, nodes, button.mark)"
                v-for="button in toolbar"
              >
                <span
                  v-if="button.icon.indexOf('dashicons-') !== -1"
                  class="dashicons"
                  :class="button.icon"
                ></span>
              </button>

              <div class="dropdown" v-if="!simpleVersion">
                <button
                  class="btn btn-secondary dropdown-toggle"
                  :class="{ 'active': (nodes.paragraph.active()|| nodes.heading.active({level: 1}) || nodes.heading.active({level: 2})|| nodes.heading.active({level: 3})) }"
                  type="button"
                  @click="toggleDDP('ddph')"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >Size</button>
                <div
                  class="dropdown-menu"
                  :class="{'show':ddph}"
                  aria-labelledby="dropdownMenuButton"
                >
                  <a
                    class="dropdown-item btn btn-secondary"
                    :class="{ 'active': nodes.paragraph.active() }"
                    @click.prevent="wrapHeaders(nodes)"
                  >Normal</a>
                  <a
                    class="dropdown-item btn btn-secondary"
                    v-for="n in 3"
                    :class="{ 'active': nodes.heading.active({ level: n }) }"
                    @click.prevent="wrapHeaders(nodes, n)"
                  >H{{ n }}</a>
                </div>
              </div>
              <div class="dropdown" data-tt="Insert custom data dynamycally replaced when email is sent">
                <button
                  class="btn btn-secondary dropdown-toggle"
                  type="button"
                  @click="toggleDDP('ddpf')"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >Fields</button>
                <div
                  class="dropdown-menu"
                  :class="{'show':ddpf}"
                  aria-labelledby="dropdownMenuButton"
                >
                  <a
                    class="dropdown-item btn btn-secondary"
                    v-for="etag in emailtags"
                    @click.prevent="insertCfield(nodes, etag.model, etag.key)"
                  >{{ etag.label }}</a>
                </div>
              </div>
              <div class="dropdown" data-tt="Link selection to a Wappointment page">
                <button
                  class="btn btn-secondary dropdown-toggle"
                  type="button"
                  @click="toggleDDP('ddpl')"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >Links</button>
                <div
                  class="dropdown-menu"
                  :class="{'show':ddpl}"
                  aria-labelledby="dropdownMenuButton"
                >
                  <a
                    class="dropdown-item btn btn-secondary"
                    v-for="elink in emaillinks"
                    @click.prevent="linkToSomething(marks, elink.model, elink.key)"
                  >{{ elink.label }}</a>
                </div>
              </div>
              <div class="dropdown" v-if="definition.multiple_service_type" data-tt="Show selection only when condition is met">
                <button
                  class="btn btn-secondary dropdown-toggle"
                  :class="{ 'active': (nodes.cblockphysical.active()|| nodes.cblockskype.active() || nodes.cblockphone.active() || nodes.cblockzoom.active()) }"
                  type="button"
                  @click="toggleDDP('ddpc')"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >Show only</button>
                <div
                  class="dropdown-menu"
                  :class="{'show':ddpc}"
                  aria-labelledby="dropdownMenuButton"
                >
                  <a
                    class="dropdown-item btn btn-secondary"
                    :class="{ 'active': nodes.cblockphone.active() }"
                    @click.prevent="conditionalBlock(nodes, 'cblockphone')"
                  >For Phone appointments</a>
                  <a
                    class="dropdown-item btn btn-secondary"
                    :class="{ 'active': nodes.cblockskype.active() }"
                    @click.prevent="conditionalBlock(nodes, 'cblockskype')"
                  >For Skype appointments</a>
                  <a
                    class="dropdown-item btn btn-secondary"
                    :class="{ 'active': nodes.cblockzoom.active() }"
                    @click.prevent="conditionalBlock(nodes, 'cblockzoom')"
                  >For Zoom appointments</a>
                  <a
                    class="dropdown-item btn btn-secondary"
                    :class="{ 'active': nodes.cblockphysical.active() }"
                    @click.prevent="conditionalBlock(nodes, 'cblockphysical')"
                  >For appointments at a Location</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div slot="content" slot-scope="props"></div>

      <div>{{ value }}</div>
    </editor>
    <div class="footer-reminder" >
      <div v-if="simpleVersion">
        <div>Total characters:  {{ characterCount }}</div>
        <div v-if="definition.sms">
          <span v-if="characterCount > 160" class="small text-danger">
            {{ characterCount > 306 ? Math.ceil(characterCount/153):2 }} SMS will be sent </span> 
            <div>
              <span v-if="hasShortcodes.length>0" class="small text-muted">shortcodes <span v-for="short in hasShortcodes"> {{ short }}  </span> will increase your character count invariably</span>
            </div>
        </div> 
      </div>
      <div class="p-0 d-flex align-items-center" v-else>
        <LinkEdit :fieldValue="definition.save_appointment_text_link" fieldKey="save_appointment_text_link"></LinkEdit>
        <div class="text-muted mx-2" v-if="definition.allow_cancellation || definition.allow_rescheduling"> | </div>
        <LinkEdit v-if="definition.allow_rescheduling" :fieldValue="definition.reschedule_link" fieldKey="reschedule_link"></LinkEdit>
        <div class="text-muted mx-2" v-if="definition.allow_cancellation && definition.allow_rescheduling"> | </div>
        <LinkEdit v-if="definition.allow_cancellation" :fieldValue="definition.cancellation_link" fieldKey="cancellation_link"></LinkEdit>
      </div>
      
    </div>
  </div>
</template>

<script>
import AbstractField from '../Form/AbstractField'
import { Editor } from "tiptap"
import {
  // Nodes
  BlockquoteNode,
  CodeBlockNode,
  CodeBlockHighlightNode,
  HardBreakNode,
  HeadingNode,
  ImageNode,
  OrderedListNode,
  BulletListNode,
  ListItemNode,
  TodoItemNode,
  TodoListNode,

  // Marks
  BoldMark,
  CodeMark,
  ItalicMark,
  LinkMark,
  StrikeMark,
  UnderlineMark,

  // General Extensions
  HistoryExtension,
  PlaceholderExtension
} from "tiptap-extensions"
import CustomFieldNode from "./text-editor/CustomField.js"
import ConditionalPhoneBlockNode from "./text-editor/ConditionalPhoneBlock.js"
import ConditionalSkypeBlockNode from "./text-editor/ConditionalSkypeBlock.js"
import ConditionalZoomBlockNode from "./text-editor/ConditionalZoomBlock.js"
import ConditionalPhysicalBlockNode from "./text-editor/ConditionalPhysicalBlock.js"
import LinkEdit from "../Components/LinkEdit"

export default {
  name:'opt-tiptap',
    mixins: [AbstractField],
    components: {
        Editor,
        LinkEdit
    },
    data(){
        return {
        position: false,
        linkUrl: null,
        writingUrl: false,
        characterCount:0,
        hasShortcodes: [],
        toolbar: [
            {
            mark: "bold",
            icon: "dashicons-editor-bold"
            },
            {
            mark: "italic",
            icon: "dashicons-editor-italic"
            },
            {
            mark: "link",
            icon: "dashicons-admin-links",
            activeIcon: "dashicons-admin-links"
            },
            {
            mark: "strike",
            icon: "dashicons-editor-strikethrough"
            },
            {
            mark: "underline",
            icon: "dashicons-editor-underline"
            },
            {
            mark: "bullet_list",
            icon: "dashicons-editor-ul"
            },
            {
            mark: "ordered_list",
            icon: "dashicons-editor-ol"
            }
        ],
        ddph: false,
        ddpf: false,
        ddpl: false,
        ddpc: false,
        emaillinks: window.wappoEmailLinks,
        emailtags: window.wappoEmailTags,
        extensions: [
            new BlockquoteNode(),
            new BulletListNode(),
            new CodeBlockNode(),
            new HardBreakNode(),
            new HeadingNode({ maxLevel: 3 }),
            new ImageNode(),
            new ListItemNode(),
            new OrderedListNode(),
            new TodoItemNode(),
            new TodoListNode(),
            new BoldMark(),
            new CodeMark(),
            new ItalicMark(),
            new LinkMark(),
            new StrikeMark(),
            new UnderlineMark(),
            new HistoryExtension(),
            new PlaceholderExtension(),
            new CustomFieldNode(),
            new ConditionalPhoneBlockNode(),
            new ConditionalSkypeBlockNode(),
            new ConditionalZoomBlockNode(),
            new ConditionalPhysicalBlockNode()
        ]
        };
    },
    computed: {
      styleLinkContainer() {
        if (this.position) {
          let topPos = Math.round(this.position.top - this.position.height);
          let leftPos = Math.round(32);
          if (this.$refs.editor.state.ranges !== undefined) {
            leftPos +=
              this.$refs.editor.state.ranges[0].$from.pos -
              this.$refs.editor.state.ranges[0].$to.pos;
          }
          let position = "fixed";
          if (this.position.top == 0) {
            topPos = 0;
            leftPos = 0;
            position = "absolute";
            return "";
          }

          return (
            "display:block !important;position:" +
            position +
            ";" +
            "top:" +
            topPos +
            "px;left:" +
            leftPos +
            "px;"
          );
        }
        return "";
      }
  },
  created(){
    this.simpleVersion = this.definition.simple !== undefined && this.definition.simple === true
    if(this.simpleVersion){
      this.toolbar=[]
      this.extensions=[new CustomFieldNode(),
            new ConditionalPhoneBlockNode(),
            new ConditionalSkypeBlockNode(),
            new ConditionalZoomBlockNode(),
            new ConditionalPhysicalBlockNode()
        ]
    }
  },
    methods:{
      updateModel({ getJSON, getHTML }) {
          this.updatedValue = getJSON()
          this.hasShortcodes = this.getShortcodes(this.$refs.editor.state.doc.content)
          this.characterCount = this.$refs.editor.state.doc.content.size - 2
      },
      getShortcodes(content){
        let customfields = []
        if(content.content[0] !== undefined && content.content[0].content !== undefined && content.content[0].content.content !== undefined){
          for (let i = 0; i < content.content[0].content.content.length; i++) {
            const element = content.content[0].content.content[i]
            if(element.attrs.class !== undefined && element.attrs.class=='customfield'){
              customfields.push('['+element.attrs.src+':'+element.attrs.alt+']')
            }
            
          }
        }
        return customfields
      },
      getSelectionDimensions() {
        var sel = document.selection,
          range;
        var width = 0,
          height = 0;
        if (window.getSelection) {
          sel = window.getSelection();
          if (sel.rangeCount) {
            range = sel.getRangeAt(0).cloneRange();
            if (range.getBoundingClientRect) {
              var rect = range.getBoundingClientRect();
              width = rect.right - rect.left;
              height = rect.bottom - rect.top;
              return {
                width: width,
                height: height,
                top: rect.top,
                left: rect.left,
                right: rect.right,
                bottom: rect.bottom
              };
            }
          }
        }
        return { width: 0, height: 0, top: 0, left: 0, right: 0, bottom: 0 };
      },

      showLinkInput(marks, nodes) {
        if (!this.writingUrl) {
          this.setLink(
            marks["link"].attrs.href !== undefined ? marks["link"].attrs.href : ""
          );
        }
      },
      getActiveState(marks, nodes, mark) {
        if (marks[mark] !== undefined) {
          if (mark == "link") {
            if (marks[mark].active()) {
              if (!this.writingUrl && marks[mark].attrs.href != this.linkUrl) {
                this.resetLink();
              }
              if (this.styleLinkContainer == "") {
                this.showLinkInput(marks, nodes);
              }
            } else {
              this.resetLink();
            }
          }

          return marks[mark].active();
        }
        if (nodes[mark] !== undefined) {
          return nodes[mark].active();
        }
      },

      confirmLinkMark(marks, nodes) {
        let attrs = { href: this.linkUrl };
        marks["link"].command(attrs);
        this.resetLink();
        this.writingUrl = false;
      },

      removeLinkMark(marks, nodes) {
        let attrs = { href: "" };
        marks["link"].command(attrs);
        this.resetLink();
        this.writingUrl = false;
      },

      setLink(linkvalue = "") {
        this.linkUrl = linkvalue;
        this.position = this.getSelectionDimensions();
      },

      resetLink() {
        this.linkUrl = "";
        this.position = false;
      },

      getCommand(marks, nodes, mark) {
        if (marks[mark] !== undefined) {
          if (mark == "link") {
            marks["link"].command({ href: "http://" });
          } else {
            return marks[mark].command();
          }
        }
        if (nodes[mark] !== undefined) {
          return nodes[mark].command();
        }
      },
      setContent() {
        // set content for json object
        this.$refs.editor.setContent(
          JSON.parse(JSON.stringify(this.value)),
          true
        );

        this.$refs.editor.focus();
      },

      toggleDDP(ddpName) {
        let newVal = true;
        if (this[ddpName] == true) newVal = false;
        this.hideDropDowns();
        this[ddpName] = newVal;
      },

      hideDropDowns() {
        this.ddph = false
        this.ddpf = false
        this.ddpc = false
        this.ddpl = false
      },

      insertCfield(nodes, model, key) {
        this.hideDropDowns();
        return nodes.customfield.command({ src: model, alt: key })
      },

      linkToSomething(marks,  model, key) {
        this.hideDropDowns()
        marks["link"].command({ href: "["+model+':'+key+"]" })
        this.resetLink();
        this.writingUrl = false;
      },

      wrapHeaders(nodes, n = false) {
        if (n === false) nodes.paragraph.command();
        else nodes.heading.command({ level: n });
        this.hideDropDowns();
      },

      conditionalBlock(nodes, activateCondition) {
        let cblocks = ["cblockphone", "cblockskype", "cblockphysical", "cblockzoom"];
        let rerun = true;
        for (let index = 0; index < cblocks.length; index++) {
          const condition = cblocks[index];
          if (nodes[condition].active()) {
            nodes[condition].command();
            if (activateCondition == condition) rerun = false;
          }
        }

        if (rerun) nodes[activateCondition].command();
        this.hideDropDowns();
      }
    },
    mounted() {
      this.setContent();
    },
}
</script>
<style>
.dropdown[data-tt]{
  z-index: 99999999999;
}
.is-active {
  background-color: #ccc !important;
}
.vue-editor .ProseMirror p {
  margin-bottom: 0.4rem;
}
.conditional {
  border: 2px dashed #eaeaea;
  padding: 0;
  position: relative;
  transition: all 0.3s ease-in;
  margin: 0.2rem 0 !important;
}
.conditional:hover {
  box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.3);
  border-color: #96ccce;
}
.conditional::before {
  border-radius: 2px;
  border: 2px dashed #ccc;
  opacity: 0;
  margin-top: -23px;
  margin-left: -2px;
  width: auto;
  height: auto;
  position: absolute;
  font-family: dashicons;
  color: #898989;
  transition: all 0.3s ease-in;
}

.conditional.conditional-phone::before {
  content: "\f525";
}
.conditional.conditional-physical::before {
  content: "\f231";
}
.conditional.conditional-skype::before,
.conditional.conditional-zoom::before {
  content: "\f235";
}

.conditional:hover::before {
  position: absolute;
  opacity: 1;
  background-color: #effeff;
  padding: 0 5px;
  border-color: #96ccce;
}

.conditional.conditional-phone:hover::before {
  content: "\f525 " attr(data-tt);
}
.conditional.conditional-physical:hover::before {
  content: "\f231 " attr(data-tt);
}
.conditional.conditional-skype:hover::before,
.conditional.conditional-zoom:hover::before {
  content: "\f235 " attr(data-tt);
}

.vue-editor .ProseMirror {
  box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.3);
  border: 1px solid #ccc;
  padding: 0.4rem;
}

.vue-editor .menubar {
  padding: 0.4rem;
  background-color: #d7d7d7;
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
  position: relative;
}


.vue-editor .ProseMirror ul {
  list-style: disc;
  margin-left: 1.8rem;
}

.vue-editor .menubar .linkfield {
    padding: 0.5rem;
    background-color: #969696;
    border-radius: 0.2rem;
    display: none;
    position: absolute !important;
    top: 52px !important;
    left: 0 !important;
}

.ProseMirror-focused a {
  -webkit-user-select: all;
  user-select: all;
}
.footer-reminder{
  padding: 1rem;
  border: dashed 2px #ccc;
  text-align: center;
}
</style>

